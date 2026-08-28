<?php

require_once (__DIR__."/../src/engines/latex/Alignment.php");
require_once (__DIR__."/../src/engines/latex/Center.php");
require_once (__DIR__."/../src/engines/latex/Right.php");
require_once (__DIR__."/../src/engines/latex/Indent.php");

$left = Left(["Left", "**gras**\n\n- un\n- deux"]);
$center = Center(["Center", "**gras**"]);
$right = Right(["Right", "**gras**"]);

assert(str_contains($left, "\\begingroup\\raggedright\n\n**gras**"));
assert(str_contains($center, "\\begingroup\\centering\n\n**gras**"));
assert(str_contains($right, "\\begingroup\\raggedleft\n\n**gras**"));
assert(str_ends_with($left, "\\par\\endgroup\n"));
assert(Center(["Center"]) === "\\centering");
assert(Left(["Left"]) === "\\raggedright{}");
assert(Right(["Right"]) === "\\raggedleft{}");
assert(Indent(["Indent"]) === "\\mbox{}\\hspace*{1cm}");
assert(Indent(["Indent", "1.5"]) === "\\mbox{}\\hspace*{1.5cm}");
assert(Indent(["Indent", "99"]) === "\\mbox{}\\hspace*{10cm}");

echo "alignment tests: ok\n";
