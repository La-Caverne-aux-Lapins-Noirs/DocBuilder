<?php

/*
 * Stable fingerprint of the fully merged/resolved DocBuilder configuration.
 *
 * DocBuilder.DabsicHash is a reserved value. It is deliberately removed before
 * serialisation so that the digest never depends on itself. Associative scopes
 * are sorted by key while Dabsic tables/lists preserve their order.
 */

function DocBuilderHashArrayIsList(array $value)
{
    $index = 0;
    foreach ($value as $key => $_)
        if ($key !== $index++)
            return (false);
    return (true);
}

function DocBuilderHashWithoutSelf(array $configuration)
{
    if (isset($configuration["DocBuilder"]) && is_array($configuration["DocBuilder"]))
    {
        unset($configuration["DocBuilder"]["DabsicHash"]);
        if (!count($configuration["DocBuilder"]))
            unset($configuration["DocBuilder"]);
    }
    return ($configuration);
}

function DocBuilderCanonicalizeForHash($value)
{
    if (!is_array($value))
        return ($value);

    if (DocBuilderHashArrayIsList($value))
    {
        $out = [];
        foreach ($value as $child)
            $out[] = DocBuilderCanonicalizeForHash($child);
        return ($out);
    }

    ksort($value, SORT_STRING);
    $out = [];
    foreach ($value as $key => $child)
        $out[$key] = DocBuilderCanonicalizeForHash($child);
    return ($out);
}

function DocBuilderConfigurationHash(array $configuration)
{
    $configuration = DocBuilderHashWithoutSelf($configuration);
    $canonical = DocBuilderCanonicalizeForHash($configuration);
    $json = json_encode(
        $canonical,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
    );
    if ($json === false)
        throw new RuntimeException("Cannot canonicalize DocBuilder configuration for hashing.");
    return (hash("sha256", $json));
}

function DocBuilderInjectHash(array &$configuration, $hash)
{
    if (!isset($configuration["DocBuilder"]) || !is_array($configuration["DocBuilder"]))
        $configuration["DocBuilder"] = [];
    $configuration["DocBuilder"]["DabsicHash"] = (string)$hash;
}
