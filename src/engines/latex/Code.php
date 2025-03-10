<?php

/**
 * @throws Exception
 */
function Code(array $rules): string
{
   if (!(isset($rules[1])))
    throw new Exception("Rules is missing for code Function !");
   $language = trim($rules[1]);
   $str = "\\begin{tcolorbox}[colback=black, coltext=white, sharp corners=southwest]\n\\begin{minted}{{$language}}\n";
   $search = [';'];
   $replace =['\\;'];
   for ($i = 2; isset($rules[$i]); $i++) {
       if (isset($rules[$i + 1]))
           $str .= str_replace($search, $replace, $rules[$i]) . ";";
       else
           $str .= str_replace($search, $replace, $rules[$i]) . "\n";
   }
   $str .= "\\end{minted}\n\\end{tcolorbox}";
   $search = [" ", "\t"];
   $replace = ["\\sp", "\\tb"];
   $str = str_replace($search, $replace, $str);
   return ($str);
}