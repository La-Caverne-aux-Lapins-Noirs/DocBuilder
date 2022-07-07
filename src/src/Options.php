<?php // @codeCoverageIgnoreStart

class Options
{
    // ActivityFile (Template), Instance information, Optional style
    public $ShortOptions = "a:i:s:t:o:c:d:m:l:";
    public $LongOptions = [
	"activity:",
	"instance:",
	"style:",
	"type:",
	"output:",
	"configuration:",
	"dictionnary:",
	"medals:",
	"no-pretty",
	"keep-trace",
	"language:",
    ];
    public $Description = [
	"The Dabsic file describing the activity you want to generate the document. Mandatory.",
	"The Dabsic file describing the instance of the activity. Mandatory.",
	"The style of the documentation you want to generate. Default is /etc/technocore/default.css.",
	"The document you want to generate. (pdfa4subject, school_reports, etc.). Default is pdfa4subject.",
	"The output file. Default is stdout.",
	"DocBuilder configuration file. Default is /etc/technocore/docbuilder.dab.",
	"DocBuilder's dictionnary file. Default is /etc/technocore/dictionnary.dab.",
	"Medals directory. Default is /etc/technocore/medals.",
	"Compact the generated code.",
	"Keep generated code after rendering into PDF.",
	"Language of the generated document. Default is FR (french).",
	"The type of document to generate"
    ];
};

function GetOptions()
{
    return (new Options);
}

// @codeCoverageIgnoreEnd
