<?php
require_once (__DIR__."/../src/engines/latex/InternshipCalendar.php");

$Configuration = [
    "Internship" => [
        "ScheduleCalendar" => [
            "Format" => "HalfDayV1",
            "Country" => "FR",
            "StartDate" => "2026-09-17",
            "EndDate" => "2026-11-11",
            "Days" => [
                "D20260917" => ["Morning" => "T", "Afternoon" => "E"],
                "D20261001" => ["Morning" => "T", "Afternoon" => "T"],
                "D20261111" => ["Morning" => "F", "Afternoon" => "F"],
            ],
        ],
    ],
];

$out = InternshipCalendar(["InternshipCalendar", "Internship.ScheduleCalendar"]);
foreach ([
    "SEPTEMBRE 2026",
    "OCTOBRE 2026",
    "NOVEMBRE 2026",
    "= entreprise",
    "\\textbf{17}",
    "\\colorbox{black}",
    "\\textcolor{white}{\\textbf{T}}",
    "\\colorbox{white}",
    "\\textcolor{black}{\\textbf{E}}",
    "\\colorbox{black!22}",
    "0.158\\linewidth",
] as $needle)
    if (strpos($out, $needle) === false)
        throw new RuntimeException("Missing calendar fragment: ".$needle);

$year = _internship_calendar_render([
    "StartDate" => "2026-01-01",
    "EndDate" => "2026-12-31",
    "Days" => [],
]);
if (substr_count($year, "\\begin{minipage}[t]{0.158\\linewidth}") !== 12)
    throw new RuntimeException("A full year must render as twelve compact month blocks.");
if (substr_count($year, "\\par\\vspace{0.16cm}") !== 2)
    throw new RuntimeException("A full year must render on two rows of six months.");
if (strpos((string)($Configuration[".LatexExtraHeader"] ?? ""), "\\usepackage{xcolor}") === false)
    throw new RuntimeException("Internship calendar must request xcolor support.");

$blank = _internship_calendar_render([]);
if (strpos($blank, "à compléter") === false)
    throw new RuntimeException("Blank calendar placeholder missing.");

$failed = false;
try {
    $Configuration["Internship"]["ScheduleCalendar"]["Format"] = "Unknown";
    InternshipCalendar(["InternshipCalendar", "Internship.ScheduleCalendar"]);
}
catch (RuntimeException $e) { $failed = true; }
if (!$failed)
    throw new RuntimeException("Unknown format should fail.");

echo "internship_calendar: OK\n";
