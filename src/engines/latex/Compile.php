<?php

function Compile($conf, $str)
{
    $command =
	"pandoc /dev/stdin -o output.tex --pdf-engine=xelatex ".
	"--include-in-header {$conf[".Directory"]}configuration.tex ".
	"--columns 150 ".
	"-V papersize=a4 ".
	"-f markdown ".
	"-t latex ";
    if ($conf[".Debug"])
        $command .= "--verbose";
    $outputFileName = basename($conf[".OutputFile"]);
    $outputDir = dirname($conf[".OutputFile"]);
    $command .= "; latexmk --shell-escape -jobname={$outputFileName} -output-directory={$outputDir} output.tex ";
    print_r("Command inside Compile :". $command. "\n");

    $fd = popen($command, "w");
    // echo $str;
    fwrite($fd, $str);
    pclose($fd);
}

