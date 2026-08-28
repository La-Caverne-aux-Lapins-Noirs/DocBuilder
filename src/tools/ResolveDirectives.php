<?php

/**
 * Contexte de diagnostic du parseur de directives.
 *
 * Le parseur travaille sur le texte final produit par BuildDocument().  Les
 * positions ci-dessous sont donc celles de ce texte.  L'extrait de source et
 * la pile des directives rendent néanmoins la directive fautive immédiatement
 * identifiable, même lorsque le document a été construit depuis plusieurs
 * fragments Dabsic / txt.
 */
class DocBuilderDirectiveException extends RuntimeException
{
}

class DocBuilderDirectiveParseContext
{
    public string $source;
    public array $stack = [];

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function push(string $prefix, string $name, int $offset): void
    {
        $this->stack[] = [
            'prefix' => $prefix,
            'name' => $name,
            'offset' => $offset,
            'argument' => null,
        ];
    }

    public function renameCurrent(string $name): void
    {
        $index = count($this->stack) - 1;
        if ($index >= 0)
            $this->stack[$index]['name'] = $name;
    }

    public function setArgument(?int $argument): void
    {
        $index = count($this->stack) - 1;
        if ($index >= 0)
            $this->stack[$index]['argument'] = $argument;
    }

    public function pop(): void
    {
        array_pop($this->stack);
    }

    public function current(): ?array
    {
        if (!count($this->stack))
            return null;
        return $this->stack[count($this->stack) - 1];
    }
}

function directiveParserDisplayLength(string $text): int
{
    if (function_exists('mb_strlen'))
        return mb_strlen($text, 'UTF-8');
    if (preg_match_all('/./us', $text, $matches) !== false)
        return count($matches[0]);
    return strlen($text);
}

function directiveParserLineColumn(string $source, int $offset): array
{
    $len = strlen($source);
    $offset = max(0, min($offset, $len));
    $before = substr($source, 0, $offset);
    $line = substr_count($before, "\n") + 1;
    $lastNewline = strrpos($before, "\n");
    $lineStart = ($lastNewline === false) ? 0 : $lastNewline + 1;
    $columnText = substr($source, $lineStart, $offset - $lineStart);
    $column = directiveParserDisplayLength($columnText) + 1;
    return [$line, $column, $lineStart];
}

function directiveParserSourceLine(string $source, int $offset): array
{
    [$line, $column, $lineStart] = directiveParserLineColumn($source, $offset);
    $lineEnd = strpos($source, "\n", $lineStart);
    if ($lineEnd === false)
        $lineEnd = strlen($source);
    $text = substr($source, $lineStart, $lineEnd - $lineStart);
    return [$line, $column, $text];
}

function directiveParserExpandTabs(string $text, int $tabWidth = 4): string
{
    $out = '';
    $column = 0;
    $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false)
        $chars = str_split($text);
    foreach ($chars as $char)
    {
        if ($char === "\t")
        {
            $spaces = $tabWidth - ($column % $tabWidth);
            $out .= str_repeat(' ', $spaces);
            $column += $spaces;
        }
        else
        {
            $out .= $char;
            $column++;
        }
    }
    return $out;
}

function directiveParserCaretColumn(string $line, int $column, int $tabWidth = 4): int
{
    if ($column <= 1)
        return 1;
    $chars = preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false)
        $chars = str_split($line);
    $display = 0;
    $seen = 0;
    foreach ($chars as $char)
    {
        if ($seen >= $column - 1)
            break;
        if ($char === "\t")
            $display += $tabWidth - ($display % $tabWidth);
        else
            $display++;
        $seen++;
    }
    return $display + 1;
}

function directiveParserFormatLocation(string $source, int $offset): string
{
    [$line, $column] = directiveParserLineColumn($source, $offset);
    return "line $line, column $column";
}

function directiveParserFormatExcerpt(string $source, int $offset): string
{
    [$line, $column, $text] = directiveParserSourceLine($source, $offset);
    $shown = directiveParserExpandTabs($text);
    $caretColumn = directiveParserCaretColumn($text, $column);
    $prefix = $line . ' | ';
    return $prefix . $shown . "\n" . str_repeat(' ', strlen($prefix) + $caretColumn - 1) . '^';
}

function directiveParserFailureDump(string $source): ?string
{
    $path = '/tmp/docbuilder_failure' . date('Ymd-His') . '-' . getmypid() . '.txt';
    if (@file_put_contents($path, $source) === false)
        return null;
    return $path;
}

function directiveParserException(
    DocBuilderDirectiveParseContext $context,
    string $message,
    ?int $offset = null,
    ?int $directiveOffset = null,
    ?string $directiveName = null,
    ?int $argument = null
): DocBuilderDirectiveException
{
    if ($offset === null)
        $offset = strlen($context->source);

    $current = $context->current();
    if ($directiveOffset === null && $current !== null)
        $directiveOffset = $current['offset'];
    if ($directiveName === null && $current !== null)
        $directiveName = $current['name'];
    if ($argument === null && $current !== null)
        $argument = $current['argument'];

    $lines = [];
    $lines[] = 'DocBuilder directive error: ' . $message;
    if ($directiveName !== null && $directiveName !== '')
        $lines[] = 'Directive: ' . $directiveName;
    if ($argument !== null)
        $lines[] = 'Argument: ' . $argument;
    if ($directiveOffset !== null)
        $lines[] = 'Directive opened at: ' . directiveParserFormatLocation($context->source, $directiveOffset);
    $lines[] = 'Error detected at: ' . directiveParserFormatLocation($context->source, $offset);
    $lines[] = '';
    $lines[] = directiveParserFormatExcerpt($context->source, $offset);

    if ($directiveOffset !== null && $directiveOffset !== $offset)
    {
        [$openLine] = directiveParserLineColumn($context->source, $directiveOffset);
        [$errorLine] = directiveParserLineColumn($context->source, $offset);
        if ($openLine !== $errorLine)
        {
            $lines[] = '';
            $lines[] = 'Directive opening:';
            $lines[] = directiveParserFormatExcerpt($context->source, $directiveOffset);
        }
    }

    if (count($context->stack))
    {
        $lines[] = '';
        $lines[] = 'Directive stack:';
        foreach ($context->stack as $frame)
        {
            $label = ($frame['name'] !== '') ? $frame['name'] : '<name being parsed>';
            $suffix = ($frame['argument'] !== null) ? ', argument ' . $frame['argument'] : '';
            $lines[] = '  - ' . $label . ' (' . directiveParserFormatLocation($context->source, $frame['offset']) . $suffix . ')';
        }
    }

    $dump = directiveParserFailureDump($context->source);
    if ($dump !== null)
    {
        $lines[] = '';
        $lines[] = 'Input snapshot: ' . $dump;
    }

    return new DocBuilderDirectiveException(implode("\n", $lines));
}

/**
 * Parse du texte jusqu'à un caractère de fin éventuel ($stopChar),
 * en résolvant les directives imbriquées et en respectant les blocs
 * crochetés littéraux équilibrés.
 */
function parseText($conf, $str, &$i, $stopChar = null, array $prefixes = ['[@', '[#'], ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $out = '';
    $len = strlen($str);

    while ($i < $len)
    {
        if ($stopChar !== null && $str[$i] === $stopChar)
        {
            $i++;
            return $out;
        }

        $prefix = matchPrefix($str, $i, $prefixes);
        if ($prefix !== null)
        {
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes, $context);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes, $context);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    if ($stopChar !== null)
        throw directiveParserException($context, "Missing '$stopChar'", $i);

    return $out;
}

/**
 * Lit le nom d'une directive (non résolu) jusqu'à ';' ou ']'.
 * Retourne [nom, terminateur]
 */
function parseDirectiveName($str, &$i, ?DocBuilderDirectiveParseContext $context = null, ?int $open = null)
{
    $len = strlen($str);
    $name = '';

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
            return [$name, $str[$i]];

        $name .= $str[$i];
        $i++;
    }

    if ($context !== null)
        throw directiveParserException($context, 'Unterminated directive name', $i, $open, $name);
    throw new RuntimeException('Unterminated directive name');
}

/**
 * Parse un argument en résolvant les directives imbriquées,
 * jusqu'à ';' ou ']'.
 * Retourne [valeur, terminateur]
 */
function parseResolvedArgument($conf, $str, &$i, array $prefixes = ['[@', '[#'], ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $out = '';
    $len = strlen($str);

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
            return [$out, $str[$i]];

        $prefix = matchPrefix($str, $i, $prefixes);
        if ($prefix !== null)
        {
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes, $context);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes, $context);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw directiveParserException($context, 'Unterminated directive argument', $i);
}

/**
 * Parse un argument SANS exécuter les directives imbriquées,
 * en conservant le texte tel quel, jusqu'à ';' ou ']'.
 * Retourne [valeur, terminateur]
 */
function parseRawArgument($str, &$i, ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $out = '';
    $len = strlen($str);

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
            return [$out, $str[$i]];

        if ($str[$i] === '[')
        {
            $out .= parseRawBracketBlock($str, $i, $context);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw directiveParserException($context, 'Unterminated raw directive argument', $i);
}

/**
 * Lit un bloc crocheté complet SANS exécuter les directives imbriquées.
 * Le texte est recopié à l'identique.
 */
function parseRawBracketBlock($str, &$i, ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $len = strlen($str);
    $open = $i;

    if ($i >= $len || $str[$i] !== '[')
        throw directiveParserException($context, "parseRawBracketBlock must start on '['", $i);

    $out = '[';
    $i++;

    while ($i < $len)
    {
        if ($str[$i] === '[')
        {
            $out .= parseRawBracketBlock($str, $i, $context);
            continue;
        }

        if ($str[$i] === ']')
        {
            $out .= ']';
            $i++;
            return $out;
        }

        $out .= $str[$i];
        $i++;
    }

    throw directiveParserException($context, "Missing ']' after raw bracket block", $i, $open);
}

/**
 * Parse une directive complète à partir d'un préfixe déjà détecté.
 * Exemple : [@Gras; Damdoshi]
 *
 * Cas spécial :
 *   IfC est évalué paresseusement :
 *   - les 3 premiers arguments sont résolus normalement
 *   - les branches then/else sont conservées brutes
 *     puis seule la branche sélectionnée est résolue.
 */
function parseDirective($conf, $str, &$i, $prefix, array $prefixes = ['[@', '[#'], ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $len = strlen($str);
    $open = $i;
    $context->push($prefix, '', $open);

    $i += strlen($prefix);

    [$rawName, $terminator] = parseDirectiveName($str, $i, $context, $open);
    $name = trim($rawName);
    $context->renameCurrent($name);

    if ($name === '')
        throw directiveParserException($context, 'Directive name is empty', $i, $open, $name);

    // Directive sans argument : [@Name]
    if ($terminator === ']')
    {
        $i++;
        $result = invokeDirective($conf, $prefix, [$name], $context, $open);
        $context->pop();
        return $result;
    }

    // Consomme le ';' après le nom
    $i++;

    // Cas spécial : IfC évalue paresseusement ses branches
    if ($name === 'IfC')
    {
        $parts = [$name];

        // Arguments 1..3 résolus normalement
        for ($k = 0; $k < 3; ++$k)
        {
            $context->setArgument($k + 1);
            [$arg, $term] = parseResolvedArgument($conf, $str, $i, $prefixes, $context);
            $parts[] = $arg;

            if ($term === ';')
            {
                $i++;
                continue;
            }

            if ($term === ']')
            {
                $i++;
                $result = invokeDirective($conf, $prefix, $parts, $context, $open);
                $context->pop();
                return $result;
            }
        }

        // Branche then brute
        $context->setArgument(4);
        [$arg, $term] = parseRawArgument($str, $i, $context);
        $parts[] = $arg;

        if ($term === ';')
        {
            $i++;

            // Branche else brute
            $context->setArgument(5);
            [$arg, $term] = parseRawArgument($str, $i, $context);
            $parts[] = $arg;
        }

        if ($term !== ']')
            throw directiveParserException($context, "Missing ']' after IfC directive", $i, $open, $name);

        $i++;
        $context->setArgument(null);
        $result = invokeDirective($conf, $prefix, $parts, $context, $open);
        $context->pop();
        return $result;
    }

    // Cas général : tous les arguments sont résolus normalement
    $parts = [$name];
    $argument = 1;

    while ($i < $len)
    {
        $context->setArgument($argument);
        [$arg, $term] = parseResolvedArgument($conf, $str, $i, $prefixes, $context);
        $parts[] = $arg;

        if ($term === ';')
        {
            $i++;
            $argument++;
            continue;
        }

        if ($term === ']')
        {
            $i++;
            $context->setArgument(null);
            $result = invokeDirective($conf, $prefix, $parts, $context, $open);
            $context->pop();
            return $result;
        }
    }

    throw directiveParserException($context, "Missing ']' after directive", $i, $open, $name, $argument);
}

/**
 * Parse un bloc crocheté NON-directive, en conservant les crochets
 * dans la sortie, mais en résolvant les directives imbriquées.
 *
 * Exemple :
 *   [EF1]           -> [EF1]
 *   [abc [#Lol] ]   -> [abc <résultat de Lol> ]
 */
function parseBracketLiteral($conf, $str, &$i, array $prefixes = ['[@', '[#'], ?DocBuilderDirectiveParseContext $context = null)
{
    if ($context === null)
        $context = new DocBuilderDirectiveParseContext($str);

    $len = strlen($str);
    $open = $i;

    if ($i >= $len || $str[$i] !== '[')
        throw directiveParserException($context, "parseBracketLiteral must start on '['", $i);

    $out = '[';
    $i++;

    while ($i < $len)
    {
        if ($str[$i] === ']')
        {
            $out .= ']';
            $i++;
            return $out;
        }

        $prefix = matchPrefix($str, $i, $prefixes);
        if ($prefix !== null)
        {
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes, $context);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes, $context);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw directiveParserException($context, "Missing ']' after bracket block", $i, $open);
}

/**
 * Vérifie si l'une des séquences de préfixe commence à la position $i.
 */
function matchPrefix($str, $i, array $prefixes)
{
    foreach ($prefixes as $pfx)
    {
        if (substr($str, $i, strlen($pfx)) === $pfx)
            return $pfx;
    }
    return null;
}

/**
 * Appelle la fonction correspondant à la directive.
 *
 * $parts[0] = nom de la fonction
 * $parts[1..n] = arguments déjà résolus, sauf pour les branches paresseuses
 * éventuelles (ex: IfC).
 */
function invokeDirective($conf, $prefix, array $parts, ?DocBuilderDirectiveParseContext $context = null, ?int $open = null)
{
    if (count($parts) === 0)
    {
        if ($context !== null)
            throw directiveParserException($context, 'Empty directive', $open ?? 0, $open);
        throw new RuntimeException('Empty directive');
    }

    $name = trim($parts[0]);

    if ($name === '')
    {
        if ($context !== null)
            throw directiveParserException($context, 'Directive name is empty', $open ?? 0, $open, $name);
        throw new RuntimeException('Directive name is empty');
    }

    if (!is_callable($name))
    {
        if ($context !== null)
            throw directiveParserException($context, 'Unknown directive: ' . $name, $open ?? 0, $open, $name);
        throw new RuntimeException('Unknown directive: ' . $name);
    }

    return $name($parts);

}

function ResolveDirectives($conf, $str, array $prefixes = ['[@', '[#'])
{
    $i = 0;
    $context = new DocBuilderDirectiveParseContext($str);
    return parseText($conf, $str, $i, null, $prefixes, $context);
}
