<?php

class Options
{
    // ActivityFile (Template), Instance information, Optional style
    public $ShortOptions = "a:i:s:f:o:c:d:m:e::";
    public $LongOptions = [
	"activity:",
	"instance:",
	"style:",
	"format:",
	"output:",
	"configuration:",
	"dictionnary:",
	"medals:",
	"no-pretty",
	"keep-trace",
	"engine::",
    ];
    public $Bold = "\033[1m";
    public $Normal = "\033[0m";
    public $Description = [
	"The Dabsic file describing the activity you want to generate the document. {$bold}Mandatory{$normal}",
	"The Dabsic file describing the instance of the activity. {$bold}Mandatory{$normal}",
	"The style of the documentation you want to generate. Default is /etc/technocore/default.css",
	"The format of the document you want to generate. (PDFA4, PDFA5, web). Default is PDFA4",
	"The output file. Default is stdout",
	"DocBuilder configuration file. Default is /etc/technocore/docbuilder.dab",
	"DocBuilder's dictionnary file. Default is /etc/technocore/dictionnary.dab",
	"Medals directory. Default is /etc/technocore/medals",
	"Compact the generated code.",
	"Keep generated code after rendering into PDF.",
	"Engine to use: html or latex."
    ];
};

function GetOptions()
{
    return (new Options);
}

