<?php

/*
 * Generic signatory resolver.
 *
 * Dabsic input files are merged and resolved by mergeconf before this code is
 * called. Any scope may declare itself as a signatory with:
 *
 *   Signatory = 1
 *   As = "Role"
 *
 * As may also be a Dabsic table of strings. The same source scope is then
 * exposed under Signatories.<Role> for every declared role.
 *
 * The document itself declares the roles it accepts under Signatures. Fields
 * stored in Signatures.<Role> describe the role in the document and are merged
 * into the normalized Signatories.<Role> scope. Required is control metadata
 * and is not copied to the rendered signatory scope.
 */

function DocBuilderArrayIsList(array $value)
{
    $index = 0;
    foreach ($value as $key => $_)
        if ($key !== $index++)
            return (false);
    return (true);
}

function DocBuilderSignatoryEnabled($value)
{
    if (is_bool($value))
        return ($value);
    if (is_int($value) || is_float($value))
        return ($value != 0);
    if (is_string($value))
        return (in_array(strtolower(trim($value)), ["1", "true", "yes", "on"], true));
    return (false);
}

function DocBuilderValidRole($role)
{
    return (is_string($role) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $role) === 1);
}

function DocBuilderSignatoryRoles($value, $path)
{
    if (is_string($value))
        $roles = [$value];
    else if (is_array($value) && DocBuilderArrayIsList($value))
        $roles = $value;
    else
        throw new RuntimeException("Invalid signatory role declaration at $path.As: expected a string or a table of strings.");

    if (!count($roles))
        throw new RuntimeException("Empty signatory role declaration at $path.As.");

    $out = [];
    foreach ($roles as $role)
    {
        if (!DocBuilderValidRole($role))
            throw new RuntimeException("Invalid signatory role at $path.As: roles must be strict C identifiers.");
        if (!in_array($role, $out, true))
            $out[] = $role;
    }
    return ($out);
}

function DocBuilderDeclaredSignatureRoles(array $configuration)
{
    if (!isset($configuration["Signatures"]))
        return (NULL);
    if (!is_array($configuration["Signatures"]))
        throw new RuntimeException("Signatures must be a Dabsic scope.");

    $roles = [];
    foreach ($configuration["Signatures"] as $role => $definition)
    {
        if (!DocBuilderValidRole($role))
            throw new RuntimeException("Invalid role '$role' in Signatures: roles must be strict C identifiers.");
        if (!is_array($definition))
            throw new RuntimeException("Signatures.$role must be a Dabsic scope.");
        $roles[$role] = $definition;
    }
    return ($roles);
}

function DocBuilderSignatureRoleMetadata(array $definition)
{
    unset($definition["Required"]);
    return ($definition);
}

function DocBuilderCleanSignatoryIdentity(array $identity)
{
    unset($identity["Signatory"], $identity["As"]);
    return ($identity);
}

function DocBuilderCollectSignatories($node, $path, &$found, $declared_roles)
{
    if (!is_array($node))
        return;

    if (isset($node["Signatory"]) && DocBuilderSignatoryEnabled($node["Signatory"]))
    {
        if (!array_key_exists("As", $node))
            throw new RuntimeException("Missing As for signatory at $path.");

        foreach (DocBuilderSignatoryRoles($node["As"], $path) as $role)
        {
            if ($declared_roles !== NULL && !array_key_exists($role, $declared_roles))
                throw new RuntimeException("Signatory at $path declares undeclared role '$role'.");

            if (isset($found[$role]) && $found[$role]["path"] !== $path)
                throw new RuntimeException("Several different signatories declare the same role '$role'.");
            $found[$role] = ["path" => $path, "identity" => $node];
        }
    }

    foreach ($node as $key => $child)
    {
        // Signatories is the normalized result. Signatures contains role
        // requirements, not identities. Neither contains source identities.
        if ($key === "Signatories" || $key === "Signatures")
            continue;
        if (!is_array($child))
            continue;
        $child_path = $path === "" ? (string)$key : $path.".".$key;
        DocBuilderCollectSignatories($child, $child_path, $found, $declared_roles);
    }
}

function ResolveSignatories(array &$configuration)
{
    if (isset($configuration["Signatories"]))
        throw new RuntimeException("Signatories is a generated DocBuilder scope and must not be supplied by input Dabsic. Declare Signatory=1 and As instead.");

    $declared = DocBuilderDeclaredSignatureRoles($configuration);
    $found = [];
    DocBuilderCollectSignatories($configuration, "", $found, $declared);
    $resolved = [];

    if ($declared !== NULL)
    {
        foreach ($declared as $role => $definition)
        {
            $required = isset($definition["Required"])
                ? DocBuilderSignatoryEnabled($definition["Required"])
                : false;
            if (!isset($found[$role]))
            {
                if ($required)
                    throw new RuntimeException("Missing required signatory for role '$role'.");
                continue;
            }

            $identity = DocBuilderCleanSignatoryIdentity($found[$role]["identity"]);
            $identity = array_replace($identity, DocBuilderSignatureRoleMetadata($definition));
            $resolved[$role] = $identity;
        }
    }
    else
    {
        foreach ($found as $role => $entry)
            $resolved[$role] = DocBuilderCleanSignatoryIdentity($entry["identity"]);
    }

    if (count($resolved))
        $configuration["Signatories"] = $resolved;
}
