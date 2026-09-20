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
foreach (["SEPTEMBRE 2026", "OCTOBRE 2026", "NOVEMBRE 2026", "T = entreprise", "\\textbf{17}", "\\textbf{T}\\textbf{E}"] as $needle)
    if (strpos($out, $needle) === false)
        throw new RuntimeException("Missing calendar fragment: ".$needle);

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
