<?php
// Jason Brillante "Damdoshi"
// Hanged Bunny Studio 2014-2020
//
// LibLapin

// symbols must start with wider symbols and end with shorter one.
function color_template($cnt)
{
    $cnt = str_replace('$S', '<span class="c_code_symbol">', $cnt);
    $cnt = str_replace('$V', '<span class="c_code_variable">', $cnt);
    $cnt = str_replace('$K', '<span class="c_code_keyword">', $cnt);
    $cnt = str_replace('$T', '<span class="c_code_type">', $cnt);
    $cnt = str_replace('$C', '<span class="c_code_constant">', $cnt);
    $cnt = str_replace('$B', '<span class="c_code_strong">', $cnt);
    $cnt = str_replace('$L', '<span class="c_code_litteral">', $cnt);
    $cnt = str_replace('$M', '<span class="c_code_comment">', $cnt);
    $cnt = str_replace('$A', '&nbsp;&nbsp;&nbsp&nbsp;', $cnt);
    $cnt = str_replace('@', '</span>', $cnt);
    return ($cnt);
}

function PrettyCode($Rules, $cnt, $space = true)
{
    $cnt = str_replace("\r", "", $cnt);
    $cnt = str_replace("<", '$L&lt;', $cnt);
    $cnt = str_replace(">", '&gt;@', $cnt);
    if ($space)
    {
	$cnt = str_replace(" ", "&nbsp;", $cnt);
	$cnt = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $cnt);
    }
    else
    {
	$cnt = str_replace("(", "(<br />&nbsp;&nbsp;", $cnt);
	$cnt = str_replace(",", ",<br />&nbsp;&nbsp;", $cnt);
	$cnt = str_replace(")", "<br />)", $cnt);
    }

    foreach ([
	"EXIT_FAILURE", "EXIT_SUCCESS", "EXIT_ON_SUCCESS", "EXIT_ON_FAILURE",
	"EXIT_ON_CROSS", "GO_ON", "SWITCH_CONTEXT", "GO_DOWN", "GO_UP",
	"LOST_FOCUS", "GOT_FOCUS", "CONNECTED", "DISCONNECTED", "ENTERING",
	"LEAVING", "M_PI", "EINVAL", "ENOENT", "errno", "FD_ZERO", "FD_SET", "FD_ISSET", "FD_CLR"
    ] as $x) {
	$cnt = str_replace($x, '$S'.$x.'@', $cnt);
    }
    foreach ([
	"NULL", "false", "true", "TRANSPARENT", "BLACK", "RED", "GREEN", "BLUE",
	"PURPLE", "TEAL", "YELLOW", "WHITE", "PINK", "PINK2"
    ] as $x) {
	$cnt = str_replace($x, '$C'.$x.'@', $cnt);
    }
    foreach ([
	"struct", "return", "while", "for", "if", "typedef", "extern", "volatile",
	"register", "continue", "break", "goto", "switch", "const", "asm", "class", "template",
	"typename", "sizeof", "static", "union", "enum"
    ] as $x) {
	$cnt = str_replace($x, '$K'.$x.'@', $cnt);
    }
    foreach ([
	"unsigned", "float", "int", "double", "char", "uint64_t", "int64_t", "short", "long", "void",
	"t_bunny_pixelarray", "t_bunny_position", "t_bunny_picture", "t_bunny_clipable", "int32_t", "uint32_t",
	"int16_t", "uint16_t", "int8_t", "uint8_t"
    ] as $x) {
	$cnt = str_replace($x, '$T'.$x.'@', $cnt);
    }
    $cnt = preg_replace('/"[a-zA-Z\S0-9]+\"/', '$T${0}</span>', $cnt);
    $cnt = preg_replace("/&nbsp;t_[a-zA-Z0-9_]+/", '$T${0}@', $cnt);
    $cnt = preg_replace("/&nbsp;s_[a-zA-Z0-9_]+/", '$T${0}@', $cnt);
    $cnt = preg_replace("/\(t_[a-zA-Z0-9_]+/", '$T${0}@', $cnt);
    $cnt = preg_replace("/^t_[a-zA-Z0-9_]+/", '$T${0}@', $cnt);

    // Nom de fonction
    $cnt = preg_replace("/[a-zA-Z_][a-zA-Z_0-9]*\(/", '$S${0}@(', $cnt);
    $cnt = str_replace("(@(", "@(", $cnt);

    // Variables
    $cnt = preg_replace("/([a-zA-Z_][a-zA-Z_0-9]*)@&nbsp;(\**)([a-zA-Z_][a-zA-Z_0-9]*)/", '${1}@&nbsp;${2}$V${3}@', $cnt);

    $cnt = preg_replace("/BKS_[a-zA-Z0-9_]+/", '$S${0}@', $cnt);
    $cnt = preg_replace("/BMB_[a-zA-Z0-9_]+/", '$S${0}@', $cnt);
    $cnt = preg_replace("/BA_[a-zA-Z0-9_]+/", '$S${0}@', $cnt);
    $cnt = preg_replace("/LAST_[a-zA-Z0-9_]+/", '$S${0}@', $cnt);
    $cnt = preg_replace('@//.*@', '<span style="color: red;">${0}</span>', $cnt);
    $cnt = preg_replace('@#[a-z]+@ ', '<span style="color: darkblue;">${0}</span>', $cnt);

    $cnt = str_replace("\n", "<br/>", $cnt);
    return (color_template($cnt));
}
