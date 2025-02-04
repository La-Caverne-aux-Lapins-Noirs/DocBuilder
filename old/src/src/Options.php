<?php // @codeCoverageIgnoreStart

class Options
{
    // ActivityFile (Template), Instance information, Optional style
    public $ShortOptions = "a:i:s:o:c:d:m:l:b:n k";
    public $LongOptions = [
	"activity:",
	"instance:",
	"style:",
	"output:",
	"configuration:",
	"dictionnary:",
	"medals:",
	"language:",
	"bulk:",
	"no-pretty",
	"keep-trace"
    ];
    public $Description = [
	"The Dabsic file describing the specificities of the type of document you want to generate the document. Mandatory",
	"The Dabsic file describing the specificities of the document. Mandatory",
	"The style of the documentation you want to generate. Default is /etc/technocore/default.css",
	"The output file. Default is stdout",
	"DocBuilder configuration file. Configure information about the company. Default is /etc/technocore/docbuilder.dab",
	"DocBuilder's dictionnary file. Default is /etc/technocore/dictionnary.dab",
	"Medals directory. Default is /etc/technocore/medals",
	"Language of the generated document. Default is FR (french)",
	"Define configuration file that indicates all options and allows bulk processing",
	"Compact the generated code",
	"Keep generated code after rendering into PDF"
    ];
};

function GetOptions()
{
    return (new Options);
}

// @codeCoverageIgnoreEnd
