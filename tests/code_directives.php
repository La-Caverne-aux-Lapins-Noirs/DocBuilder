<?php

require_once (__DIR__."/../src/tools/ResolveDirectives.php");
foreach (['Code', 'Hint', 'Bonus', 'Warning', 'History', 'If', 'Variable'] as $engine)
    require_once (__DIR__."/../src/engines/latex/".$engine.".php");

function code_test($condition, $message)
{
    if (!$condition)
        throw new RuntimeException($message);
}

function code_visible($value)
{
    return str_replace(['\\sp', '\\tb'], [" ", "\t"], $value);
}

$body = <<<'C'
#include <stdlib.h>
int main(void)
{
  int a[2] = {1, 2};
  const char *text = "[@DefinitelyMissing;x]; [#include]; [";
  char close = ']';
  /* ] [ ; [@DefinitelyMissing] */
  // ] [ ;
  if (test_abs())
    {
      efputchar('!');
      efputchar('\n');
      return (EXIT_FAILURE);
    }
  return (a[0]);
}
C;

$source = 'before [@Code;c;'.$body.'] after';
$rendered = code_visible(ResolveDirectives([], $source));
code_test(str_contains($rendered, "\\begin{minted}{c}\n".$body."\n\\end{minted}"),
    'C source must survive unchanged, including semicolons, arrays, strings and comments');
code_test(str_starts_with($rendered, 'before ') && str_ends_with($rendered, ' after'),
    'The code closing bracket must not consume surrounding text');
code_test(code_visible(ResolveDirectives([], '[#Code;c;'.$body.']')) ===
    code_visible(Code(['Code', 'c', $body])), 'Both directive prefixes must work');

$console = "$> echo hello; echo world\nhello\nworld\n[0] can't\n";
code_test(str_contains(code_visible(ResolveDirectives([], '[@Code;console;'.$console.']')),
    $console), 'Console output must remain literal');

foreach (['Hint', 'Bonus', 'Warning', 'History'] as $name)
{
    $rendered = ResolveDirectives([], '[@'.$name.';first; second; third]');
    code_test(str_contains($rendered, 'first; second; third'),
        $name.' must retain prose after semicolons');
}
$nested = ResolveDirectives([], '[@Hint;before; [@Code;c;'.$body.'] after]');
code_test(str_contains(code_visible($nested), $body), 'Code inside a text box must stay literal');

$Configuration = ['Enabled' => 'yes'];
$rendered = ResolveDirectives($Configuration,
    '[@IfC;Enabled;==;yes;[@Code;c;'.$body.'];[@DefinitelyMissing]]');
code_test(str_contains(code_visible($rendered), $body), 'Selected Code branch must render');
$rendered = ResolveDirectives($Configuration,
    '[@IfC;Enabled;==;no;[@Code;c;'.$body.'];selected]');
code_test($rendered === 'selected', 'Unselected Code branch must remain lazy');

try
{
    ResolveDirectives([], "first line\n[@Code;c;int a[2];");
    throw new RuntimeException('An unterminated Code directive must fail');
}
catch (DocBuilderDirectiveException $exception)
{
    code_test(str_contains($exception->getMessage(), "Missing ']' after Code directive"),
        'Unterminated Code must report its own delimiter');
    code_test(str_contains($exception->getMessage(), 'line 2, column 1'),
        'Code diagnostics must preserve source positions');
}

echo "code directives: OK\n";
