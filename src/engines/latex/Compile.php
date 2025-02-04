<?php

function Compile($conf, $str)
{
    $command =
	"pandoc /dev/stdin -o {$conf[".OutputFile"]} --pdf-engine=xelatex ".
	"--include-in-header {$conf[".Directory"]}configuration.tex ".
	"--columns 150 ".
	"-V papersize=a4 ".
	"-f markdown ".
	"-t pdf "
	;
    if ($conf[".Debug"])
	$command .= "--verbose";
    $fd = popen($command, "w");
    // echo $str;
    fwrite($fd, $str);
    pclose($fd);
}

