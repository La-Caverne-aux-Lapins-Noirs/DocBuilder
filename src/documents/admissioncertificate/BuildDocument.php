<?php

function admission_certificate_conf(array $conf, array $path, $default = "")
{
    $value = $conf;
    foreach ($path as $key)
    {
        if (!is_array($value) || !array_key_exists($key, $value))
            return ($default);
        $value = $value[$key];
    }
    return ($value);
}

function admission_certificate_text($value)
{
    $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, "UTF-8");
    return (LatexEscape($value));
}

function admission_certificate_lines($value)
{
    $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $lines = preg_split('/\s*(?:\r\n|\r|\n)\s*/u', trim($value));
    $out = [];
    foreach ($lines as $line)
        if (($line = trim($line)) != "")
            $out[] = LatexEscape($line);
    return (implode("\\\\\n", $out));
}

function admission_certificate_bool($value)
{
    if (is_bool($value))
        return ($value);
    return (in_array(strtolower(trim((string)$value)), ["1", "true", "yes", "oui", "on"], true));
}

function admission_certificate_gender_word($gender, $male, $female, $neutral)
{
    $gender = strtolower(trim((string)$gender));
    if (in_array($gender, ["f", "female", "femme", "madame", "mme"], true))
        return ($female);
    if (in_array($gender, ["m", "male", "homme", "monsieur", "mr", "m."], true))
        return ($male);
    return ($neutral);
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";

    $school_name = admission_certificate_text(admission_certificate_conf($conf, ["School", "Name"]));
    $school_address_value = admission_certificate_conf($conf, ["School", "Address"]);
    $school_address = admission_certificate_lines($school_address_value);
    $school_address_footer = admission_certificate_text(preg_replace('/\s*(?:\r\n|\r|\n)\s*/u', ', ', trim((string)$school_address_value)));
    $school_phone = admission_certificate_text(admission_certificate_conf($conf, ["School", "Phone"]));
    $school_mail = admission_certificate_text(admission_certificate_conf($conf, ["School", "Mail"]));
    $school_logo = admission_certificate_conf($conf, ["School", "DocumentLogo"], admission_certificate_conf($conf, ["School", "Logo"]));
    $school_main_info = admission_certificate_text(admission_certificate_conf($conf, ["School", "MainInfo"]));
    $school_school_info = admission_certificate_text(admission_certificate_conf($conf, ["School", "SchoolInfo"]));
    $school_formation_info = admission_certificate_text(admission_certificate_conf($conf, ["School", "FormationInfo"]));
    $school_alternation_info = admission_certificate_text(admission_certificate_conf($conf, ["School", "AlternationInfo"]));

    $director_identity = admission_certificate_text(admission_certificate_conf($conf, ["Signatories", "Director", "Identity"]));
    $director_role = admission_certificate_text(admission_certificate_conf($conf, ["Signatories", "Director", "Role"], "Directeur(trice)"));
    $director_mail = admission_certificate_text(admission_certificate_conf($conf, ["Signatories", "Director", "Mail"]));
    $director_signature = admission_certificate_conf($conf, ["Signatories", "Director", "Signature"]);

    $student_identity = admission_certificate_text(admission_certificate_conf($conf, ["Student", "Identity"]));
    $student_birth_date = admission_certificate_text(admission_certificate_conf($conf, ["Student", "BirthDate"]));
    $student_birth_place = admission_certificate_text(admission_certificate_conf($conf, ["Student", "BirthPlace"]));
    $student_nationality = admission_certificate_text(admission_certificate_conf($conf, ["Student", "Nationality"]));
    $student_gender = admission_certificate_conf($conf, ["Student", "Gender"]);

    $born = admission_certificate_gender_word($student_gender, "né", "née", "né(e)");
    $admitted = admission_certificate_gender_word($student_gender, "admis", "admise", "admis(e)");

    $qualifier = trim((string)admission_certificate_conf($conf, ["TitleQualifier"], admission_certificate_conf($conf, ["Admission", "TitleQualifier"])));
    $title = "ATTESTATION D’ADMISSION".($qualifier != "" ? " ".$qualifier : "");
    $admission_adverb = $qualifier != "" ? " définitivement" : "";

    $target_class = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "TargetClass"]));
    $target_year_label = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "TargetYearLabel"]));
    $entry_date = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "EntryDate"]));
    $entry_label = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "EntryLabel"]));
    $school_year = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "SchoolYear"]));
    $prospect_date = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "ProspectRegistrationDate"]));

    $required_amount = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "RequiredAmount"]));
    $payment_confirmed = admission_certificate_bool(admission_certificate_conf($conf, ["Admission", "PaymentConfirmed"], false));

    $issue_place = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "IssuePlace"]));
    $issue_date = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "IssueDate"]));
    $reference = admission_certificate_text(admission_certificate_conf($conf, ["Admission", "Reference"]));
    $automatic_signature = admission_certificate_bool(admission_certificate_conf($conf, ["Admission", "AutomaticSignature"], false));

    $footer_parts = array_values(array_filter([
        $school_main_info,
        $school_school_info,
        $school_formation_info,
        $school_alternation_info,
    ], static fn($value) => trim((string)$value) != ""));
    if (!count($footer_parts))
    {
        $fallback = trim(implode(" - ", array_filter([$school_name, $school_address_footer])));
        if ($fallback != "")
            $footer_parts[] = $fallback;
    }
    $school_contact = trim(implode(" - ", array_filter([
        $school_phone == "" ? "" : "Tél. : ".$school_phone,
        $school_mail == "" ? "" : "E-mail : ".$school_mail,
    ])));
    if ($school_contact != "")
        $footer_parts[] = $school_contact;
    $footer_text = implode(" ", $footer_parts);
?>
---
geometry: left=2.6cm, right=2.6cm, top=3.35cm, bottom=3.15cm, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 1cm
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{1.85cm}
\setlength{\headsep}{0.65cm}
\setlength{\footskip}{1.45cm}
\setlength{\parindent}{1cm}
\setlength{\parskip}{0.65em}
\renewcommand{\headrulewidth}{0.55pt}
\renewcommand{\footrulewidth}{0.45pt}

\fancyhead[L]{%
\begin{minipage}[c][1.55cm][c]{0.68\textwidth}
{\small\bfseries <?=$title; ?>}
\end{minipage}%
}
\fancyhead[R]{%
\begin{minipage}[c][1.55cm][c]{0.24\textwidth}
\raggedleft
<?php if ($school_logo != "") { ?>[@Image;<?=$school_logo; ?>;width=3.6cm;height=1.45cm]<?php } ?>
\end{minipage}%
}

\fancyfoot[C]{%
\begin{minipage}{0.96\textwidth}
\centering\scriptsize <?=$footer_text; ?>
\end{minipage}%
}

\noindent
\begin{minipage}[t]{0.76\textwidth}
\setlength{\parindent}{0pt}
\raggedright
{\small
\textbf{<?=$school_name; ?>}\\
<?=$school_address; ?>
<?php if ($school_phone != "") { ?>\\Tél. : <?=$school_phone; ?><?php } ?>
<?php if ($school_mail != "") { ?>\\E-mail : <?=$school_mail; ?><?php } ?>
<?php if ($director_identity != "") { ?>\\Direction : <?=$director_identity; ?><?php } ?>
<?php if ($director_mail != "" && $director_mail != $school_mail) { ?>\\Contact : <?=$director_mail; ?><?php } ?>
}
\end{minipage}%
\hfill
\begin{minipage}[t]{0.20\textwidth}
\setlength{\parindent}{0pt}
\raggedleft
<?php if ($reference != "") { ?>{\scriptsize Réf. <?=$reference; ?>}<?php } ?>
\end{minipage}

\vspace{0.8cm}

\begin{center}
{\LARGE\bfseries <?=$title; ?>}
\end{center}

\vspace{0.55cm}

\indent Je soussigné<?php if ($director_identity != "") { ?> \textbf{<?=$director_identity; ?>}<?php } ?>,
<?=$director_role; ?>, atteste que :

\begin{center}
\begin{minipage}{0.86\textwidth}
\centering
{\large\bfseries <?=$student_identity; ?>}

<?php if ($student_birth_date != "" || $student_birth_place != "") { ?>
<?=$born; ?><?php if ($student_birth_date != "") { ?> le <?=$student_birth_date; ?><?php } ?><?php if ($student_birth_place != "") { ?> à <?=$student_birth_place; ?><?php } ?>,
<?php } ?>
<?php if ($student_nationality != "") { ?>de nationalité <?=$student_nationality; ?>,<?php } ?>
\end{minipage}
\end{center}

\indent est<?=$admission_adverb; ?> <?=$admitted; ?> au sein de notre établissement dans le cursus
\textbf{<?=$target_class; ?>}<?php if ($target_year_label != "") { ?>, en <?=$target_year_label; ?><?php } ?>,
au titre de l’année scolaire \textbf{<?=$school_year; ?>}.

\indent La date d’entrée en formation est prévue<?php if ($entry_date != "") { ?> le \textbf{<?=$entry_date; ?>}<?php } else if ($entry_label != "") { ?> pour \textbf{<?=$entry_label; ?>}<?php } ?>.

<?php if ($prospect_date != "") { ?>\indent Le dossier de candidature a été enregistré par l’établissement le <?=$prospect_date; ?>.

<?php } ?>\begin{admissionfinancialbox}
\indent <?php if ($payment_confirmed) { ?>Le dossier a été complété et le règlement des frais exigibles à l’admission,
d’un montant de \textbf{<?=$required_amount; ?>}, a été enregistré.<?php } else { ?>Les frais exigibles au titre de cette admission s’élèvent à
\textbf{<?=$required_amount; ?>}.<?php } ?>
\end{admissionfinancialbox}

\indent La présente attestation est délivrée à l’intéressé(e) afin de servir et valoir ce que de droit,
notamment pour l’accomplissement de ses démarches administratives relatives à la poursuite de ses études en France.

\indent Nous restons à disposition des autorités compétentes pour toute vérification ou information complémentaire.

\vfill

\begin{flushright}
Fait à <?=$issue_place; ?>, le \textbf{<?=$issue_date; ?>}\\[0.4em]
<?=$director_identity; ?>\\
<?=$director_role; ?>
<?php if ($automatic_signature && $director_signature != "") { ?>

[@Image;<?=$director_signature; ?>;width=4cm;height=2cm]
<?php } else { ?>

\vspace{1.5cm}
<?php } ?>
\end{flushright}
<?php
}
