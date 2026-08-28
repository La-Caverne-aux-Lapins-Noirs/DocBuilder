<?php

require_once (__DIR__."/../src/tools/ResolveDirectives.php");

function DiagnosticEcho($rules)
{
    return ($rules[1] ?? "");
}

function diagnostic_assert($condition, $message)
{
    if (!$condition)
        throw new RuntimeException("resolve directives diagnostics test failed: ".$message);
}

function diagnostic_exception($source)
{
    try
    {
        ResolveDirectives([], $source);
    }
    catch (RuntimeException $exception)
    {
        return ($exception->getMessage());
    }
    throw new RuntimeException("Expected ResolveDirectives to fail.");
}

diagnostic_assert(
    ResolveDirectives([], "before [@DiagnosticEcho;inside] after") === "before inside after",
    "successful directives changed behaviour"
);

$message = diagnostic_exception("first line\n[@DiagnosticEcho;unfinished");
diagnostic_assert(str_contains($message, "Unterminated directive argument"), "missing primary error");
diagnostic_assert(str_contains($message, "Directive: DiagnosticEcho"), "missing directive name");
diagnostic_assert(str_contains($message, "Argument: 1"), "missing argument number");
diagnostic_assert(str_contains($message, "Directive opened at: line 2, column 1"), "missing directive opening position");
diagnostic_assert(str_contains($message, "Error detected at: line 2"), "missing error position");
diagnostic_assert(str_contains($message, "2 | [@DiagnosticEcho;unfinished"), "missing source excerpt");
diagnostic_assert(str_contains($message, "Directive stack:"), "missing directive stack");

$message = diagnostic_exception("prefix\n  [@DefinitelyMissing;value]");
diagnostic_assert(str_contains($message, "Unknown directive: DefinitelyMissing"), "unknown directive not diagnosed");
diagnostic_assert(str_contains($message, "Directive opened at: line 2, column 3"), "unknown directive location is wrong");

$message = diagnostic_exception("[@DiagnosticEcho;outer [@DefinitelyMissing;inner]]");
diagnostic_assert(str_contains($message, "- DiagnosticEcho (line 1, column 1, argument 1)"), "outer directive absent from stack");
diagnostic_assert(str_contains($message, "- DefinitelyMissing (line 1, column 24"), "nested directive absent from stack");

$message = diagnostic_exception("éé [@DefinitelyMissing;value]");
diagnostic_assert(str_contains($message, "Directive opened at: line 1, column 4"), "UTF-8 column counting is wrong");

echo "resolve directives diagnostics: OK\n";
