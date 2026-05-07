<?php

function	Usage() { ?>

Usage is:

    ./docbuilder [-i ... | -m ...]+ [-o ...]? [-d]?

    -i [file]+		Configuration files
    -m [address=value]+	Edit fields of previously loaded configuration
    -o output_file	Output file
    -d			Print on stdout generated document before compilation
    
<?php }

