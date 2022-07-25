<?php
// @codeCoverageIgnoreStart
// Devrait changer vers un /opt/truc, mais pour l'instant je ne me rappelle plus.
@define("DOCBUILDER_DEFAULT_PATH", "/opt/technocore/docbuilder");
@define("DOCBUILDER_DEFAULT_CONFIGURATION", DOCBUILDER_DEFAULT_PATH."/configuration.dab");
@define("DOCBUILDER_DEFAULT_STYLE", DOCBUILDER_DEFAULT_PATH."/default.css");
// Le dictionnaire est commun avec evaluator
@define("DOCBUILDER_DEFAULT_DICTIONNARY", "/opt/technocore/dictionnary.dab");
// Les médailles sont communes avec l'infosphere
@define("DOCBUILDER_DEFAULT_MEDALS_DIR", "/opt/technocore/medals");
//
@define("DOCBUILDER_DEFAULT_TYPE", "subject");

@define("NO_BASE64", false);

$Types = [
    "subject" => [
	"engine" => "html",
	"page" => [21, 29.7],
	"pager" => "a4_decorated",
	"mandatory" => [
	    "matter",
	    "company",
	    "activity",
	    "student",
	    "limit_date",
	    "codename",
	    "generation_date",
	    "token"
	]
    ],
    "school_report" => [
	"engine" => "html",
	"page" => [21, 29.7],
	"pager" => "a4_administrative",
	"mandatory" => [
	    // A remplir
	]
    ],
    "letter" => [
	"engine" => "html",
	"page" => [21, 29.7],
	"pager" => "a4_administrative",
	"mandatory" => [
	    "company", "company_mail", "generation_date"
	]
    ]
];

// @codeCoverageIgnoreEnd
