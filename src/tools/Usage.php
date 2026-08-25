<?php

function	Usage() { ?>

Usage is:

    ./docbuilder [-i ... | -m ...]+ [-o ...]? [-d]? [--blank]? [--hash-only]? [--hash-file file]?

    -i [file]+		Configuration files
    -m [address=value]+	Edit fields of previously loaded configuration
    -o output_file	Output file
    -d			Print on stdout generated document before compilation
    --blank		Allow missing required signatories when generating a blank template
    --hash-only	Print DocBuilder.DabsicHash and do not render the document
    --hash-file file	Write DocBuilder.DabsicHash to file and continue rendering
    
<?php }

