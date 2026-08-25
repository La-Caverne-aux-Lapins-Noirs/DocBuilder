<?php
$Configuration = ["DocBuilder" => ["DabsicHash" => str_repeat("a", 64)]];
require_once (__DIR__."/../src/engines/latex/Image.php");

[$min, $max] = DocBuilderImageRandomAngleRange("-20:20");
assert($min === -20.0 && $max === 20.0);
[$min, $max] = DocBuilderImageRandomAngleRange("12");
assert($min === -12.0 && $max === 12.0);

$a = DocBuilderImageDeterministicAngle("stamp.png", [-20.0, 20.0], 0);
$b = DocBuilderImageDeterministicAngle("stamp.png", [-20.0, 20.0], 0);
assert($a === $b);
assert($a >= -20.0 && $a <= 20.0);
$Configuration["DocBuilder"]["DabsicHash"] = str_repeat("b", 64);
$c = DocBuilderImageDeterministicAngle("stamp.png", [-20.0, 20.0], 0);
assert($c !== $a);
$Configuration["DocBuilder"]["DabsicHash"] = str_repeat("a", 64);

$out = Image(["Image", "stamp.png", "width=4cm", "height=3cm", "random_angle=-20:20"]);
assert(str_contains($out, "origin=c"));
assert(str_contains($out, "angle="));
assert(!str_contains($out, "random_angle"));

echo "image tests: ok\n";
