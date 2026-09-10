<?php

/*
 * Invoice document renderer.
 *
 * The Dabsic document only provides data.  Rendering the repeated invoice
 * lines and all monetary calculations deliberately live here because neither
 * Dabsic nor DocBuilder's text directives currently provide iteration.
 *
 * Canonical input:
 *
 * {Products
 *   [
 *     Content = "Tuition fee"
 *     Quantity = 1
 *     Price = 1000.00       ' unit price, excluding VAT, in currency units
 *     VAT = 20              ' percentage
 *   ]
 * }
 *
 * Items is accepted as an alias for Products.  PriceCents/UnitPriceCents can
 * be used when the caller already stores exact integer cents.  When no array
 * is supplied, Invoice.Label + Invoice.Amount form one compatibility line.
 */

function InvoiceArrayIsList(array $array)
{
    $index = 0;
    foreach ($array as $key => $_)
    {
        if ($key !== $index)
            return (false);
        ++$index;
    }
    return (true);
}

function InvoiceScalar($value, $default = "")
{
    if (is_string($value) || is_numeric($value))
        return ((string)$value);
    return ((string)$default);
}

function InvoiceDecimal($value, $default = 0.0)
{
    if (is_int($value) || is_float($value))
        return ((float)$value);
    if (!is_string($value))
        return ((float)$default);

    $value = trim(str_replace(["\xC2\xA0", "\xE2\x80\xAF", " ", "'"], "", $value));
    if ($value === "")
        return ((float)$default);

    // Keep the final comma/dot as decimal separator and remove the others.
    $comma = strrpos($value, ",");
    $dot = strrpos($value, ".");
    $decimal = false;
    if ($comma !== false || $dot !== false)
        $decimal = ($comma !== false && ($dot === false || $comma > $dot)) ? "," : ".";

    $clean = preg_replace('/[^0-9+\-.,]/u', '', $value);
    if ($clean === null || $clean === "")
        return ((float)$default);
    if ($decimal !== false)
    {
        $position = strrpos($clean, $decimal);
        $integer = substr($clean, 0, $position);
        $fraction = substr($clean, $position + 1);
        $integer = str_replace([",", "."], "", $integer);
        $fraction = str_replace([",", "."], "", $fraction);
        $clean = $integer.".".$fraction;
    }
    else
        $clean = str_replace([",", "."], "", $clean);

    return (is_numeric($clean) ? (float)$clean : (float)$default);
}

function InvoiceMoneyToCents($value)
{
    return ((int)round(InvoiceDecimal($value, 0.0) * 100.0));
}

function InvoiceQuantityToUnits($value)
{
    return ((int)round(InvoiceDecimal($value, 1.0) * 10000.0));
}

function InvoiceVATToBasisPoints($value)
{
    return ((int)round(InvoiceDecimal($value, 0.0) * 100.0));
}

function InvoiceMulDivRound($a, $b, $divisor)
{
    $a = (int)$a;
    $b = (int)$b;
    $divisor = (int)$divisor;
    if ($divisor <= 0)
        return (0);
    $product = $a * $b;
    $half = intdiv($divisor, 2);
    if ($product >= 0)
        return (intdiv($product + $half, $divisor));
    return (-intdiv(-$product + $half, $divisor));
}

function InvoiceLatex($value)
{
    $value = InvoiceScalar($value);
    return (strtr($value, [
        "\\" => "\\textbackslash{}",
        "{" => "\\{",
        "}" => "\\}",
        "$" => "\\$",
        "&" => "\\&",
        "#" => "\\#",
        "%" => "\\%",
        "_" => "\\_",
        "~" => "\\textasciitilde{}",
        "^" => "\\textasciicircum{}",
    ]));
}

function InvoiceLatexLines($value)
{
    $value = str_replace(["\r\n", "\r"], "\n", InvoiceScalar($value));
    $lines = array_values(array_filter(array_map('trim', explode("\n", $value)), function($line) {
        return ($line !== "");
    }));
    return (implode(" \\\\ ", array_map('InvoiceLatex', $lines)));
}

function InvoiceMoney($cents, $currency = "EUR")
{
    $negative = (int)$cents < 0;
    $cents = abs((int)$cents);
    $whole = intdiv($cents, 100);
    $fraction = $cents % 100;
    $whole = number_format($whole, 0, ',', ' ');
    $whole = str_replace(' ', '\\,', $whole);
    $suffix = strtoupper(trim((string)$currency)) === "EUR" ? "~€" : "~".InvoiceLatex(strtoupper(trim((string)$currency)));
    return (($negative ? "-" : "").$whole.",".sprintf("%02d", $fraction).$suffix);
}

function InvoiceQuantity($units)
{
    $negative = (int)$units < 0;
    $units = abs((int)$units);
    $whole = intdiv($units, 10000);
    $fraction = sprintf("%04d", $units % 10000);
    $fraction = rtrim($fraction, "0");
    return (($negative ? "-" : "").$whole.($fraction === "" ? "" : ",".$fraction));
}

function InvoiceVATRate($basis_points)
{
    $basis_points = (int)$basis_points;
    $whole = intdiv(abs($basis_points), 100);
    $fraction = abs($basis_points) % 100;
    $label = ($basis_points < 0 ? "-" : "").$whole;
    if ($fraction)
        $label .= ",".rtrim(sprintf("%02d", $fraction), "0");
    return ($label."\\,\\%");
}

function InvoiceNormalizeItems(array $conf)
{
    $raw = $conf["Products"] ?? ($conf["Items"] ?? []);
    if (is_array($raw) && count($raw) && !InvoiceArrayIsList($raw))
        $raw = [$raw];
    if (!is_array($raw))
        $raw = [];

    $items = [];
    foreach ($raw as $item)
    {
        if (!is_array($item))
            continue ;
        $content = $item["Content"] ?? ($item["Description"] ?? ($item["Label"] ?? ""));
        $quantity = InvoiceQuantityToUnits($item["Quantity"] ?? 1);
        if (array_key_exists("PriceCents", $item))
            $unit_price = (int)$item["PriceCents"];
        else if (array_key_exists("UnitPriceCents", $item))
            $unit_price = (int)$item["UnitPriceCents"];
        else
            $unit_price = InvoiceMoneyToCents($item["Price"] ?? ($item["UnitPrice"] ?? 0));
        $vat = InvoiceVATToBasisPoints($item["VAT"] ?? ($item["TaxRate"] ?? 0));
        $items[] = [
            "content" => InvoiceScalar($content, "Prestation"),
            "quantity" => $quantity,
            "unit_price" => $unit_price,
            "vat" => $vat,
        ];
    }

    if (!count($items))
    {
        $invoice = isset($conf["Invoice"]) && is_array($conf["Invoice"]) ? $conf["Invoice"] : [];
        $quantity = InvoiceQuantityToUnits($invoice["Quantity"] ?? 1);
        $unit_price = NULL;
        if (array_key_exists("UnitPriceCents", $invoice))
            $unit_price = (int)$invoice["UnitPriceCents"];
        else if (array_key_exists("UnitPrice", $invoice) && trim(InvoiceScalar($invoice["UnitPrice"])) !== "")
            $unit_price = InvoiceMoneyToCents($invoice["UnitPrice"]);

        if ($unit_price === NULL)
        {
            // Historical Infosphere billing provides Invoice.Amount as the
            // complete invoice amount. Keep that path compatible while the
            // manual document page can use UnitPrice + Quantity.
            if (array_key_exists("AmountCents", $invoice))
                $amount = (int)$invoice["AmountCents"];
            else
                $amount = InvoiceMoneyToCents($invoice["Amount"] ?? 0);
            $unit_price = $quantity == 10000 ? $amount : InvoiceMulDivRound($amount, 10000, max(1, $quantity));
        }
        $items[] = [
            "content" => InvoiceScalar($invoice["Label"] ?? "Prestation", "Prestation"),
            "quantity" => $quantity,
            "unit_price" => $unit_price,
            "vat" => InvoiceVATToBasisPoints($invoice["VAT"] ?? 0),
        ];
    }
    return ($items);
}

function InvoicePrepare(array $conf)
{
    $items = InvoiceNormalizeItems($conf);
    $prepared = [];
    $vat_groups = [];
    $total_ht = 0;

    foreach ($items as $item)
    {
        $line_ht = InvoiceMulDivRound($item["unit_price"], $item["quantity"], 10000);
        $prepared[] = $item + ["line_ht" => $line_ht];
        $total_ht += $line_ht;
        $rate = (string)$item["vat"];
        if (!isset($vat_groups[$rate]))
            $vat_groups[$rate] = ["rate" => $item["vat"], "base" => 0, "vat" => 0];
        $vat_groups[$rate]["base"] += $line_ht;
    }

    // VAT is rounded once for each tax rate, after the taxable bases have
    // been accumulated. This avoids losing cents when several small lines
    // share the same rate.
    $total_vat = 0;
    foreach ($vat_groups as &$group)
    {
        $group["vat"] = InvoiceMulDivRound($group["base"], $group["rate"], 10000);
        $total_vat += $group["vat"];
    }
    unset($group);
    uasort($vat_groups, function($a, $b) { return ($a["rate"] <=> $b["rate"]); });

    return ([
        "items" => $prepared,
        "vat_groups" => array_values($vat_groups),
        "total_ht" => $total_ht,
        "total_vat" => $total_vat,
        "total_ttc" => $total_ht + $total_vat,
    ]);
}

function InvoiceFirstIdentity(array $conf)
{
    foreach (["Finance", "FinancialResponsible", "Parent", "Student"] as $key)
        if (isset($conf[$key]) && is_array($conf[$key]))
        {
            $identity = trim(InvoiceScalar($conf[$key]["Identity"] ?? ""));
            if ($identity !== "")
                return ($conf[$key]);
        }
    return ([]);
}

function InvoicePersonBlock(array $person)
{
    $identity = trim(InvoiceScalar($person["Identity"] ?? ""));
    if ($identity === "")
        $identity = trim(InvoiceScalar($person["FirstName"] ?? "")." ".InvoiceScalar($person["FamilyName"] ?? ""));
    $lines = [];
    if ($identity !== "")
        $lines[] = "\\textbf{".InvoiceLatex($identity)."}";
    $has_postal_address = false;
    foreach (["Address", "PostalAddress"] as $key)
        if (!empty($person[$key]))
        {
            $lines[] = InvoiceLatexLines($person[$key]);
            $has_postal_address = true;
            break ;
        }
    $postal = trim(InvoiceScalar($person["PostalCode"] ?? "")." ".InvoiceScalar($person["City"] ?? ""));
    if ($postal !== "" && (empty($person["Address"]) || strpos(InvoiceScalar($person["Address"]), $postal) === false))
    {
        $lines[] = InvoiceLatex($postal);
        $has_postal_address = true;
    }
    if (!empty($person["Country"]))
    {
        $lines[] = InvoiceLatex($person["Country"]);
        $has_postal_address = true;
    }
    if (!$has_postal_address && !empty($person["Mail"]))
        $lines[] = InvoiceLatex($person["Mail"]);
    return (implode(" \\\\ ", array_filter($lines)));
}

function InvoiceSchoolBlock(array $school)
{
    $name = $school["LegalName"] ?? ($school["Name"] ?? ($school["FrName"] ?? ""));
    $address = $school["LegalAddress"] ?? ($school["Address"] ?? "");
    $lines = [];
    if (trim(InvoiceScalar($name)) !== "")
        $lines[] = "\\textbf{".InvoiceLatex($name)."}";
    if (trim(InvoiceScalar($address)) !== "")
        $lines[] = InvoiceLatexLines($address);
    if (!empty($school["Mail"]))
        $lines[] = InvoiceLatex($school["Mail"]);
    if (!empty($school["Phone"]))
        $lines[] = InvoiceLatex($school["Phone"]);
    return (implode(" \\\\ ", array_filter($lines)));
}

function InvoiceBillingInformationParts($value)
{
    $lines = preg_split("/(?:\r\n|\r|\n)/", InvoiceScalar($value));
    $intro = [];
    $details = [];
    $in_details = false;

    foreach ($lines as $line)
    {
        $trim = trim((string)$line);
        if (preg_match('/^(RIB|IBAN|BIC|SWIFT|MOTIF|REFERENCE|RÉFÉRENCE|REF|TITULAIRE)\s*[:\-]/iu', $trim))
            $in_details = true;
        if ($in_details)
            $details[] = $trim;
        else
            $intro[] = $trim;
    }
    return ([
        "intro" => trim(implode("\n", $intro)),
        "details" => trim(implode("\n", $details)),
    ]);
}

function InvoiceBuildHeaderFooter(array $conf, $reference = "")
{
    $invoice_heading = $reference !== ""
        ? "Facture n\\textdegree{} ".InvoiceLatex($reference)
        : "Facture";
    if (isset($conf["Header"]) && is_array($conf["Header"]))
    {
        foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $side)
            if (isset($conf["Header"][$key]))
            {
                $content = $conf["Header"][$key];
                if ($side == "L")
                    $content .= "\\\\[-0.05cm]{\\small\\bfseries ".$invoice_heading."}";
                if ($side == "R")
                    $content = "\\raisebox{-0.28cm}[0pt][0pt]{".$content."}";
                echo "\\fancyhead[$side]{".$content."}\n";
            }
        if (!isset($conf["Header"]["Left"]))
            echo "\\fancyhead[L]{\\textbf{".$invoice_heading."}}\n";
    }
    else if (isset($conf["Header"]) && is_string($conf["Header"]))
        echo "\\fancyhead[C]{".$conf["Header"]."\\\\[-0.05cm]{\\small\\bfseries ".$invoice_heading."}}\n";
    else
        echo "\\fancyhead[L]{\\textbf{".$invoice_heading."}}\n";

    if (isset($conf["Footer"]) && is_string($conf["Footer"]))
    {
        echo "\\fancyfoot[L]{%\n";
        echo "  \\begin{minipage}[t]{0.96\\textwidth}\\vspace{0pt}%\n";
        echo $conf["Footer"]."\n";
        echo "  \\end{minipage}%\n}\n";
    }
    else if (isset($conf["Footer"]) && is_array($conf["Footer"]))
        foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $side)
            if (isset($conf["Footer"][$key]))
                echo "\\fancyfoot[$side]{".$conf["Footer"][$key]."}\n";
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";
    $invoice = isset($conf["Invoice"]) && is_array($conf["Invoice"]) ? $conf["Invoice"] : [];
    $school = isset($conf["School"]) && is_array($conf["School"]) ? $conf["School"] : [];
    $recipient = InvoiceFirstIdentity($conf);
    $prepared = InvoicePrepare($conf);
    $currency = InvoiceScalar($invoice["Currency"] ?? "EUR", "EUR");
    $reference = trim(InvoiceScalar($invoice["Reference"] ?? ""));
    $date = trim(InvoiceScalar($invoice["IssueDateLabel"] ?? ($invoice["DateLabel"] ?? ($conf["Generation"]["Date"] ?? ""))));
    $due = trim(InvoiceScalar($invoice["DueDateLabel"] ?? ""));
    $type = trim(InvoiceScalar($invoice["TypeLabel"] ?? ""));
    $billing = InvoiceBillingInformationParts($conf["BillingInformation"] ?? "");
?>
---
geometry: left=2cm, right=2cm, top=2.6cm, bottom=2.6cm, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
output: pdf_document
lang: fr-FR
documentclass: article
indent: 0pt
header-includes:
  - \usepackage[table]{xcolor}
  - \usepackage{colortbl}
pdf-engine: xelatex
---
\pagestyle{fancy}
\fancyhf{}
\setlength{\headheight}{2.1cm}
\setlength{\headsep}{0.45cm}
\setlength{\footskip}{1.25cm}
\setlength{\parindent}{0pt}
\renewcommand{\arraystretch}{1.16}
<?php InvoiceBuildHeaderFooter($conf, $reference); ?>

\vspace{0.45cm}

\noindent
\begin{minipage}[t]{0.43\textwidth}
\vspace{0pt}
<?=InvoiceSchoolBlock($school); ?>
\end{minipage}
\hfill
\begin{minipage}[t]{0.43\textwidth}
\vspace*{0.85cm}
<?=InvoicePersonBlock($recipient); ?>
\end{minipage}

\vspace{0.25cm}

\begin{flushright}
{\small
<?php if ($date !== "") { ?>Émise le <?=InvoiceLatex($date); ?><?php } ?>
<?php if ($due !== "") { ?>\\ Échéance : <?=InvoiceLatex($due); ?><?php } ?>
<?php if ($type !== "") { ?>\\ <?=InvoiceLatex($type); ?><?php } ?>
}
\end{flushright}

\vspace{0.35cm}

\begin{longtable}{@{}>{\raggedright\arraybackslash}p{7.2cm}>{\raggedleft\arraybackslash}p{1.25cm}>{\raggedleft\arraybackslash}p{2.65cm}>{\raggedleft\arraybackslash}p{1.45cm}>{\raggedleft\arraybackslash}p{2.85cm}@{}}
\toprule
\textbf{Désignation} & \textbf{Qté} & \textbf{PU HT} & \textbf{TVA} & \textbf{Total HT} \\
\midrule
\endfirsthead
\toprule
\textbf{Désignation} & \textbf{Qté} & \textbf{PU HT} & \textbf{TVA} & \textbf{Total HT} \\
\midrule
\endhead
\midrule
\multicolumn{5}{r}{\scriptsize Suite de la facture page suivante} \\
\endfoot
\bottomrule
\endlastfoot
<?php foreach ($prepared["items"] as $item) { ?>
<?=InvoiceLatex($item["content"]); ?> & <?=InvoiceQuantity($item["quantity"]); ?> & <?=InvoiceMoney($item["unit_price"], $currency); ?> & <?=InvoiceVATRate($item["vat"]); ?> & <?=InvoiceMoney($item["line_ht"], $currency); ?> \\
<?php } ?>
\end{longtable}

<?php
    $invoice_totals = [
        ["label" => "Total HT", "value" => $prepared["total_ht"], "strong" => false],
        ["label" => "Total TVA", "value" => $prepared["total_vat"], "strong" => false],
        ["label" => "Total TTC", "value" => $prepared["total_ttc"], "strong" => true],
    ];
    $invoice_summary_rows = max(count($prepared["vat_groups"]), count($invoice_totals));
?>
\noindent\begin{tabularx}{\textwidth}{@{}>{\raggedleft\arraybackslash}p{2.65cm}>{\raggedleft\arraybackslash}p{1.35cm}>{\raggedleft\arraybackslash}p{2.65cm}X>{\raggedleft\arraybackslash}p{2.25cm}>{\raggedleft\arraybackslash}p{2.85cm}@{}}
\toprule
\multicolumn{3}{@{}l}{\textbf{Détail de la TVA}} & & \multicolumn{2}{r@{}}{\textbf{Totaux}} \\
\midrule
\textbf{Base HT} & \textbf{Taux} & \textbf{TVA} & & & \\
<?php for ($i = 0; $i < $invoice_summary_rows; ++$i) {
    $group = $prepared["vat_groups"][$i] ?? null;
    $total = $invoice_totals[$i] ?? null;
?>
<?php if ($group !== null) { ?><?=InvoiceMoney($group["base"], $currency); ?> & <?=InvoiceVATRate($group["rate"]); ?> & <?=InvoiceMoney($group["vat"], $currency); ?><?php } else { ?> & &<?php } ?> & &
<?php if ($total !== null && $total["strong"]) { ?>\textbf{<?=InvoiceLatex($total["label"]); ?>} & \textbf{<?=InvoiceMoney($total["value"], $currency); ?>}<?php } else if ($total !== null) { ?><?=InvoiceLatex($total["label"]); ?> & <?=InvoiceMoney($total["value"], $currency); ?><?php } else { ?> &<?php } ?> \\
<?php } ?>
\bottomrule
\end{tabularx}
<?php if (!empty($invoice["VATExemption"])) { ?>

\smallskip
{\scriptsize <?=InvoiceLatex($invoice["VATExemption"]); ?>}
<?php } ?>

<?php if ($billing["intro"] !== "" || $billing["details"] !== "") { ?>
\vspace{0.7cm}

\noindent\fcolorbox{black!30}{black!6}{%
\begin{minipage}{0.965\textwidth}
\centering
\textbf{Informations de paiement}
<?php if ($billing["intro"] !== "") { ?>

\smallskip
{\small <?=InvoiceLatexLines($billing["intro"]); ?>}
<?php } ?>
<?php if ($billing["details"] !== "") { ?>

\smallskip
\raggedright
\hspace*{1.5em}\begin{minipage}{0.92\textwidth}
<?=InvoiceLatexLines($billing["details"]); ?>
\end{minipage}
<?php } ?>
\end{minipage}}
<?php } ?>

<?php if (!empty($invoice["Note"])) { ?>
\vspace{0.45cm}

{\small <?=InvoiceLatexLines($invoice["Note"]); ?>}
<?php } ?>
<?php
}
