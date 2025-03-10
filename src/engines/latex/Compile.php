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
    $command .= "; latexmk --shell-escape -jobname={$conf[".OutputFile"]} output.tex ";

    $fd = popen($command, "w");
    // echo $str;
    fwrite($fd, $str);
    pclose($fd);
}

