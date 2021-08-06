<?php

function ToRoman($n)
{
  $map = [
    'M' => 1000,
    'CM' => 900,
    'D' => 500,
    'CD' => 400,
    'C' => 100,
    'XC' => 90,
    'L' => 50,
    'XL' => 40,
    'X' => 10,
    'IX' => 9,
    'V' => 5,
    'IV' => 4,
    'I' => 1
  ];

  $str = "";
  while ($n > 0)
    foreach ($map as $roman => $int)
      if($n >= $int)
      {
        $n -= $int;
        $str .= $roman;
        break;
      }
  return $str;
}

