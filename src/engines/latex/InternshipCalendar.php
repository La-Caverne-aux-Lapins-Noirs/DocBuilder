<?php

/*
** InternshipCalendar
** ------------------
** Render a HalfDayV1 internship calendar stored as ordinary Dabsic data.
**
** Usage from a document body:
**
**   [@InternshipCalendar;Internship.ScheduleCalendar]
**
** The directive deliberately reads the resolved configuration tree instead of
** receiving generated markup from the document producer.  The signed Dabsic
** therefore remains the source of truth and can be reused by other consumers
** (calendar/session synchronization, audit, etc.).
*/

function _internship_calendar_configuration_path($path)
{
    global $Configuration;

    $path = trim((string)$path);
    if ($path === "")
        return (NULL);
    $node = $Configuration;
    foreach (explode(".", $path) as $part)
    {
        if ($part === "" || !is_array($node) || !array_key_exists($part, $node))
            return (NULL);
        $node = $node[$part];
    }
    return ($node);
}

function _internship_calendar_iso_date($value)
{
    $value = trim((string)$value);
    if (!preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/D', $value, $m))
        return (NULL);
    if (!checkdate((int)$m[2], (int)$m[3], (int)$m[1]))
        return (NULL);
    return (sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]));
}

function _internship_calendar_month_names()
{
    return ([
        1 => "JANVIER",
        2 => "FÉVRIER",
        3 => "MARS",
        4 => "AVRIL",
        5 => "MAI",
        6 => "JUIN",
        7 => "JUILLET",
        8 => "AOÛT",
        9 => "SEPTEMBRE",
        10 => "OCTOBRE",
        11 => "NOVEMBRE",
        12 => "DÉCEMBRE",
    ]);
}

function _internship_calendar_status($value)
{
    $value = strtoupper(trim((string)$value));
    return (in_array($value, ["T", "E", "F"], true) ? $value : "");
}

function _internship_calendar_days(array $calendar)
{
    $out = [];
    $days = isset($calendar["Days"]) && is_array($calendar["Days"])
        ? $calendar["Days"] : [];
    foreach ($days as $key => $halves)
    {
        if (!preg_match('/^D(\\d{4})(\\d{2})(\\d{2})$/D', (string)$key, $m))
            continue ;
        $iso = sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        if (_internship_calendar_iso_date($iso) === NULL || !is_array($halves))
            continue ;
        $out[$iso] = [
            "Morning" => _internship_calendar_status($halves["Morning"] ?? ""),
            "Afternoon" => _internship_calendar_status($halves["Afternoon"] ?? ""),
        ];
    }
    return ($out);
}

function _internship_calendar_cell_status($status)
{
    $status = _internship_calendar_status($status);
    if ($status === "")
        return ("\\phantom{T}");
    return ("\\textbf{".$status."}");
}

function _internship_calendar_cell($date, $start, $end, array $days)
{
    if (!$date || $date < $start || $date > $end)
        return ("\\phantom{00}");

    $iso = $date->format('Y-m-d');
    $halves = $days[$iso] ?? ["Morning" => "", "Afternoon" => ""];
    $morning = _internship_calendar_cell_status($halves["Morning"] ?? "");
    $afternoon = _internship_calendar_cell_status($halves["Afternoon"] ?? "");
    return (
        "\\shortstack[c]{{\\scriptsize\\textbf{".$date->format('j')."}}\\\\".
        "{\\tiny ".$morning.$afternoon."}}"
    );
}

function _internship_calendar_month($year, $month, $start, $end, array $days)
{
    $names = _internship_calendar_month_names();
    $first = DateTimeImmutable::createFromFormat('!Y-n-j', ((int)$year).'-'.((int)$month).'-1');
    if (!$first)
        return ("");
    $days_in_month = (int)$first->format('t');
    $offset = (int)$first->format('N') - 1; // Monday = 0.
    $cells = $offset + $days_in_month;
    $weeks = (int)ceil($cells / 7);

    $out = "\\begin{minipage}[t]{0.315\\linewidth}\n".
        "\\centering\n".
        "\\setlength{\\tabcolsep}{0.8pt}\n".
        "\\renewcommand{\\arraystretch}{1.18}\n".
        "{\\scriptsize\n".
        "\\begin{tabular}{|c|c|c|c|c|c|c|}\n".
        "\\hline\n".
        "\\multicolumn{7}{|c|}{\\textbf{".$names[(int)$month]." ".(int)$year."}}\\\\\n".
        "\\hline\n".
        "{\\tiny L} & {\\tiny M} & {\\tiny M} & {\\tiny J} & {\\tiny V} & {\\tiny S} & {\\tiny D} \\\\"."\n".
        "\\hline\n";

    for ($week = 0; $week < $weeks; ++$week)
    {
        $row = [];
        for ($dow = 0; $dow < 7; ++$dow)
        {
            $slot = $week * 7 + $dow;
            $day = $slot - $offset + 1;
            $date = NULL;
            if ($day >= 1 && $day <= $days_in_month)
                $date = $first->setDate((int)$year, (int)$month, $day);
            $row[] = _internship_calendar_cell($date, $start, $end, $days);
        }
        $out .= implode(" & ", $row)." \\\\\n\\hline\n";
    }

    return ($out."\\end{tabular}\n}\n\\end{minipage}");
}

function _internship_calendar_render(array $calendar)
{
    $start_iso = _internship_calendar_iso_date($calendar["StartDate"] ?? "");
    $end_iso = _internship_calendar_iso_date($calendar["EndDate"] ?? "");
    if ($start_iso === NULL || $end_iso === NULL)
        return ("\\textit{Calendrier de stage à compléter.}");

    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $start_iso);
    $end = DateTimeImmutable::createFromFormat('!Y-m-d', $end_iso);
    if (!$start || !$end || $end < $start)
        return ("\\textit{Calendrier de stage invalide.}");

    $first_month = $start->modify('first day of this month');
    $last_month = $end->modify('first day of this month');
    $months = [];
    for ($cursor = $first_month; $cursor <= $last_month; $cursor = $cursor->modify('+1 month'))
    {
        $months[] = [
            "year" => (int)$cursor->format('Y'),
            "month" => (int)$cursor->format('n'),
        ];
        if (count($months) > 49)
            throw new RuntimeException("Internship calendar spans too many months.");
    }

    $days = _internship_calendar_days($calendar);
    $out = "\\begingroup\n".
        "\\small\n".
        "\\noindent\\textbf{Légende :} T = entreprise, E = école, F = jour férié. ".
        "Dans chaque case, la première lettre vaut pour le matin et la seconde pour l'après-midi.\\par\n".
        "\\vspace{0.18cm}\n";

    foreach (array_chunk($months, 3) as $row)
    {
        $out .= "\\noindent\\hfill\n";
        foreach ($row as $i => $entry)
        {
            if ($i > 0)
                $out .= "\\hfill\n";
            $out .= _internship_calendar_month(
                $entry["year"], $entry["month"], $start, $end, $days
            )."\n";
        }
        $out .= "\\hfill\\mbox{}\\par\\vspace{0.24cm}\n";
    }

    return ($out."\\endgroup\n");
}

function InternshipCalendar($rules)
{
    $path = isset($rules[1]) ? trim((string)$rules[1]) : "";
    if ($path === "")
        throw new RuntimeException("InternshipCalendar requires a Dabsic path.");

    $calendar = _internship_calendar_configuration_path($path);
    if ($calendar === NULL)
        return ("\\textit{Calendrier de stage à compléter.}");
    if (!is_array($calendar))
        throw new RuntimeException("InternshipCalendar path '$path' is not a Dabsic scope.");

    $format = trim((string)($calendar["Format"] ?? "HalfDayV1"));
    if ($format !== "" && $format !== "HalfDayV1")
        throw new RuntimeException("Unsupported internship calendar format '$format'.");
    return (_internship_calendar_render($calendar));
}
