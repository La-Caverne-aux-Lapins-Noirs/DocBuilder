<?php

/**
 * Parse du texte jusqu'à un caractère de fin éventuel ($stopChar),
 * en résolvant les directives imbriquées et en respectant les blocs
 * crochetés littéraux équilibrés.
 */
function parseText($conf, $str, &$i, $stopChar = null, array $prefixes = ['[@', '[#'])
{
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
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    if ($stopChar !== null)
    {
        throw new RuntimeException(
            "Missing '{$stopChar}' near: " . substr($str, max(0, $i - 20), 40)
        );
    }

    return $out;
}

/**
 * Lit le nom d'une directive (non résolu) jusqu'à ';' ou ']'.
 * Retourne [nom, terminateur]
 */
function parseDirectiveName($str, &$i)
{
    $len = strlen($str);
    $name = '';

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
        {
            return [$name, $str[$i]];
        }

        $name .= $str[$i];
        $i++;
    }

    throw new RuntimeException("Unterminated directive name");
}

/**
 * Parse un argument en résolvant les directives imbriquées,
 * jusqu'à ';' ou ']'.
 * Retourne [valeur, terminateur]
 */
function parseResolvedArgument($conf, $str, &$i, array $prefixes = ['[@', '[#'])
{
    $out = '';
    $len = strlen($str);

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
        {
            return [$out, $str[$i]];
        }

        $prefix = matchPrefix($str, $i, $prefixes);
        if ($prefix !== null)
        {
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw new RuntimeException("Unterminated directive argument");
}

/**
 * Parse un argument SANS exécuter les directives imbriquées,
 * en conservant le texte tel quel, jusqu'à ';' ou ']'.
 * Retourne [valeur, terminateur]
 */
function parseRawArgument($str, &$i)
{
    $out = '';
    $len = strlen($str);

    while ($i < $len)
    {
        if ($str[$i] === ';' || $str[$i] === ']')
        {
            return [$out, $str[$i]];
        }

        if ($str[$i] === '[')
        {
            $out .= parseRawBracketBlock($str, $i);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw new RuntimeException("Unterminated raw directive argument");
}

/**
 * Lit un bloc crocheté complet SANS exécuter les directives imbriquées.
 * Le texte est recopié à l'identique.
 */
function parseRawBracketBlock($str, &$i)
{
    $len = strlen($str);

    if ($i >= $len || $str[$i] !== '[')
    {
        throw new RuntimeException("parseRawBracketBlock must start on '['");
    }

    $out = '[';
    $i++;

    while ($i < $len)
    {
        if ($str[$i] === '[')
        {
            $out .= parseRawBracketBlock($str, $i);
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

    throw new RuntimeException(
        "Missing ']' after raw bracket block near: " . substr($str, max(0, $i - 20), 40)
    );
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
function parseDirective($conf, $str, &$i, $prefix, array $prefixes = ['[@', '[#'])
{
    $len = strlen($str);
    $open = $i;

    $i += strlen($prefix);

    [$rawName, $terminator] = parseDirectiveName($str, $i);
    $name = trim($rawName);

    if ($name === '')
        throw new RuntimeException("Directive name is empty");

    // Directive sans argument : [@Name]
    if ($terminator === ']')
    {
        $i++;
        return invokeDirective($conf, $prefix, [$name]);
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
            [$arg, $term] = parseResolvedArgument($conf, $str, $i, $prefixes);
            $parts[] = $arg;

            if ($term === ';')
            {
                $i++;
                continue;
            }

            if ($term === ']')
            {
                $i++;
                return invokeDirective($conf, $prefix, $parts);
            }
        }

        // Branche then brute
        [$arg, $term] = parseRawArgument($str, $i);
        $parts[] = $arg;

        if ($term === ';')
        {
            $i++;

            // Branche else brute
            [$arg, $term] = parseRawArgument($str, $i);
            $parts[] = $arg;
        }

        if ($term !== ']')
        {
            file_put_contents("/tmp/docbuilder_failure" . date("Ymd-His"), $str);
            $line = substr_count(substr($str, 0, $open), "\n") + 1;
            throw new RuntimeException(
                "Missing ']' after IfC directive opened on line $line."
            );
        }

        $i++;
        return invokeDirective($conf, $prefix, $parts);
    }

    // Cas général : tous les arguments sont résolus normalement
    $parts = [$name];

    while ($i < $len)
    {
        [$arg, $term] = parseResolvedArgument($conf, $str, $i, $prefixes);
        $parts[] = $arg;

        if ($term === ';')
        {
            $i++;
            continue;
        }

        if ($term === ']')
        {
            $i++;
            return invokeDirective($conf, $prefix, $parts);
        }
    }

    file_put_contents("/tmp/docbuilder_failure" . date("Ymd-His"), $str);
    $line = substr_count(substr($str, 0, $open), "\n") + 1;
    throw new RuntimeException(
        "Missing ']' after directive opened on line $line."
    );
}

/**
 * Parse un bloc crocheté NON-directive, en conservant les crochets
 * dans la sortie, mais en résolvant les directives imbriquées.
 *
 * Exemple :
 *   [EF1]           -> [EF1]
 *   [abc [#Lol] ]   -> [abc <résultat de Lol> ]
 */
function parseBracketLiteral($conf, $str, &$i, array $prefixes = ['[@', '[#'])
{
    $len = strlen($str);

    if ($i >= $len || $str[$i] !== '[')
    {
        throw new RuntimeException("parseBracketLiteral must start on '['");
    }

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
            $out .= parseDirective($conf, $str, $i, $prefix, $prefixes);
            continue;
        }

        if ($str[$i] === '[')
        {
            $out .= parseBracketLiteral($conf, $str, $i, $prefixes);
            continue;
        }

        $out .= $str[$i];
        $i++;
    }

    throw new RuntimeException(
        "Missing ']' after bracket block near: " . substr($str, max(0, $i - 20), 40)
    );
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
function invokeDirective($conf, $prefix, array $parts)
{
    if (count($parts) === 0)
        throw new RuntimeException("Empty directive");

    $name = trim($parts[0]);

    if ($name === '')
        throw new RuntimeException("Directive name is empty");

    if (!is_callable($name))
        throw new RuntimeException("Unknown directive: " . $name);

    return $name($parts);
}

function ResolveDirectives($conf, $str, array $prefixes = ['[@', '[#'])
{
    $i = 0;
    return parseText($conf, $str, $i, null, $prefixes);
}

