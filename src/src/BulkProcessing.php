<?php

function ExtractName($str)
{
    return (pathinfo($str, PATHINFO_FILENAME));
}

function BulkProcessing($bulk)
{
    global $argv;
    
    if (($conf = LoadDabsic($bulk)) == NULL)
	return (false);
    $generated = [];
    $keep_trace = @$conf["KeepTrace"] ? "--keep-trace" : "";
    $no_pretty = @$conf["NoPretty"] ? "--no-pretty" : "";
    if (isset($conf["Output"]))
	$conf["OutputDirectory"] = pathinfo($conf["Output"], PATHINFO_DIRNAME);
    $output_dir = @$conf["OutputDirectory"];
    foreach ([
	"Style",
	"Dictionnary",
	"Medal",
	"Configuration",
	"Activity",
	"Instance",
	"Language"
    ] as $f) {
	if (!is_array(@$conf[$f]))
	{
	    if (!@strlen($conf[$f]))
		$conf[$f] = [""];
	    else
		$conf[$f] = [$conf[$f]];
	}
    }
    
    foreach ($conf["Style"] as $style)
    {
	if ($style != "")
	    $style = " -s $style ";
	foreach ($conf["Dictionnary"] as $dictionnary)
	{
	    if ($dictionnary != "")
		$dictionnary = " -d $dictionnary ";
	    foreach ($conf["Medal"] as $medal)
	    {
		if ($medal != "")
		    $medal = " -m $medal ";
		foreach ($conf["Configuration"] as $configuration)
		{
		    if ($configuration != "")
			$configuration = " -c $configuration ";
		    foreach ($conf["Activity"] as $activity)
		    {
			if ($activity != "")
			    $activity = " -a $activity ";
			foreach ($conf["Instance"] as $instance)
			{
			    if ($instance != "")
				$instance = " -i $instance ";
			    foreach ($conf["Language"] as $language)
			    {
				if ($language != "")
				    $language = " -l $language ";

				// Configuration du document à générer...
				$gen = $output_dir."/.".uniqid().".pdf";
				if (!isset($conf["Gathering"]))
				    $generated[] = $gen;
				else
				{
				    $axis = strtolower($conf["Gathering"]);
				    if (!isset($$axis))
				    {
					echo "Unknown gathering {$conf["Gathering"]}\n";
					return (false);
				    }
				    $gerated[ExtractName($$axis)][] = $gen;
				}
				XSystem(str_replace("\n", "", "
				    {$argv[0]}
				    $configuration
				    $activity
				    $instance
				    $dictionnary
				    $medal
                                    $style
                                    $language
				    $keep_trace
                                    $no_pretty
                                    -o $gen
				"));
			    }
			}
		    }
		}
	    }
	}
    }

    if (!isset($conf["Gathering"]))
	return (MergePdf($conf["Output"], $generated));
 
    if (strlen($output_dir) == 0)
	$output_dir = "./";
    foreach ($generated as $k => $v)
	if (MergePdf("$output_dir$k.pdf", $v) == false)
	    return (false);

    return (true);
}

