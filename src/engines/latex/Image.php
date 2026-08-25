<?php

function DocBuilderImageRandomAngleRange($value)
{
    $value = trim((string)$value);
    if ($value === "")
        throw new RuntimeException("Image random_angle cannot be empty.");

    if (preg_match('/^([+-]?(?:\\d+(?:\\.\\d*)?|\\.\\d+))$/D', $value, $m))
    {
        $limit = abs((float)$m[1]);
        return ([-$limit, $limit]);
    }

    if (!preg_match('/^([+-]?(?:\\d+(?:\\.\\d*)?|\\.\\d+))\\s*:\\s*([+-]?(?:\\d+(?:\\.\\d*)?|\\.\\d+))$/D', $value, $m))
        throw new RuntimeException("Invalid Image random_angle '$value': expected min:max or a symmetric amplitude.");

    $min = (float)$m[1];
    $max = (float)$m[2];
    if ($min > $max)
        [$min, $max] = [$max, $min];
    return ([$min, $max]);
}

function DocBuilderImageDeterministicAngle($path, $range, $occurrence)
{
    global $Configuration;

    [$min, $max] = $range;
    if ($min == $max)
        return ($min);

    $document_hash = (string)($Configuration["DocBuilder"]["DabsicHash"] ?? "");
    $seed = hash("sha256", $document_hash."\0".$occurrence."\0".$path."\0".$min.":".$max);
    $unit = hexdec(substr($seed, 0, 8)) / 4294967295.0;
    return ($min + ($max - $min) * $unit);
}

function Image($rules)
{
    static $image_occurrence = 0;

    $path = $rules[1] ?? "";
    $options = [];
    $random_angle = NULL;
    $has_angle = false;
    $has_origin = false;

    foreach (array_slice($rules, 2) as $option)
    {
        $option = trim((string)$option);
        if ($option === "")
            continue;

        if (preg_match('/^random_angle\\s*=\\s*(.+)$/iD', $option, $m))
        {
            if ($random_angle !== NULL)
                throw new RuntimeException("Image random_angle specified more than once.");
            $random_angle = DocBuilderImageRandomAngleRange($m[1]);
            continue;
        }
        if (preg_match('/^angle\\s*=/iD', $option))
            $has_angle = true;
        if (preg_match('/^origin\\s*=/iD', $option))
            $has_origin = true;
        $options[] = $option;
    }

    if ($random_angle !== NULL)
    {
        if ($has_angle)
            throw new RuntimeException("Image cannot combine angle= with random_angle=.");
        $angle = DocBuilderImageDeterministicAngle($path, $random_angle, $image_occurrence++);
        if (!$has_origin)
            $options[] = "origin=c";
        $options[] = "angle=".rtrim(rtrim(sprintf("%.3F", $angle), "0"), ".");
    }
    else
        $image_occurrence++;

    $options[] = "keepaspectratio";
    return ("\\includegraphics[".implode(",", $options)."]{{$path}}");
}
