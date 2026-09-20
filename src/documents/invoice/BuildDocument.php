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
        $details = $item["Details"] ?? ($item["LineDetails"] ?? ($item["Note"] ?? ""));
        $items[] = [
            "content" => InvoiceScalar($content, "Prestation"),
            "details" => trim(InvoiceScalar($details)),
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
            "details" => trim(InvoiceScalar($invoice["LineDetails"] ?? ($invoice["InstallmentSummary"] ?? ""))),
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

function InvoiceIdentityLabel(array $person)
{
    $identity = trim(InvoiceScalar($person["Identity"] ?? ""));
    if ($identity !== "")
        return ($identity);
    return (trim(InvoiceScalar($person["FirstName"] ?? "")." ".InvoiceScalar($person["FamilyName"] ?? "")));
}

function InvoiceFinancialResponsible(array $conf)
{
    foreach (["Finance", "FinancialResponsible", "Parent", "Student"] as $key)
        if (isset($conf[$key]) && is_array($conf[$key]) && InvoiceIdentityLabel($conf[$key]) !== "")
            return ($conf[$key]);
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

function InvoiceFragment($value)
{
    if (is_array($value))
    {
        $out = "";
        foreach ($value as $fragment)
            if (is_scalar($fragment))
                $out .= (string)$fragment."\n";
        return ($out);
    }
    return (is_scalar($value) ? (string)$value : "");
}

function InvoiceDimension(array $layout, $key, $default)
{
    $value = isset($layout[$key]) ? trim((string)$layout[$key]) : "";
    if ($value === "" || preg_match('/^[0-9]+(?:\\.[0-9]+)?(?:cm|mm|pt|in)$/D', $value) !== 1)
        return ($default);
    return ($value);
}

function InvoiceBuildHeader(array $conf, $reference = "")
{
    $invoice_heading = $reference !== ""
        ? "Facture n\\textdegree{} ".InvoiceLatex($reference)
        : "Facture";

    if (isset($conf["Header"]) && is_array($conf["Header"]))
    {
        if (isset($conf["Header"]["Left"]) && InvoiceFragment($conf["Header"]["Left"]) !== "")
            echo "\\fancyhead[L]{".InvoiceFragment($conf["Header"]["Left"]).
                "\\\\[-0.05cm]{\\small\\bfseries ".$invoice_heading."}}\n";
        else
            echo "\\fancyhead[L]{{\\small\\bfseries ".$invoice_heading."}}\n";
        if (isset($conf["Header"]["Center"]))
            echo "\\fancyhead[C]{".InvoiceFragment($conf["Header"]["Center"])."}\n";
        if (isset($conf["Header"]["Right"]))
            echo "\\fancyhead[R]{\\raisebox{-0.35cm}{".
                InvoiceFragment($conf["Header"]["Right"])."}}\n";
        return ;
    }
    if (isset($conf["Header"]) && is_string($conf["Header"]))
    {
        echo "\\fancyhead[C]{".$conf["Header"]."\\\\[-0.05cm]{\\small\\bfseries ".
            $invoice_heading."}}\n";
        return ;
    }
    echo "\\fancyhead[L]{{\\small\\bfseries ".$invoice_heading."}}\n";
}

function InvoiceRegularFooter($footer, $footer_height)
{
    return ("\\begin{minipage}[t][".$footer_height."][t]{\\textwidth}\n".
        "  \\vspace{0pt}%\n".
        "  \\setlength{\\leftskip}{0pt}\\setlength{\\rightskip}{0pt}%\n".
        "  \\centering\n".
        "  ".$footer."\n".
        "\\end{minipage}%\n");
}

function InvoiceBuildFooter(array $conf, $footer_height)
{
    if (isset($conf["Footer"]) && is_string($conf["Footer"]))
    {
        echo "\\fancyfoot[L]{%\n";
        echo InvoiceRegularFooter($conf["Footer"], $footer_height);
        echo "}\n";
        return ;
    }

    if (isset($conf["Footer"]) && is_array($conf["Footer"]))
        foreach (["Left" => "L", "Center" => "C", "Right" => "R"] as $key => $side)
            if (isset($conf["Footer"][$key]))
                echo "\\fancyfoot[$side]{".InvoiceFragment($conf["Footer"][$key])."}\n";
}

function BuildDocument(&$conf)
{
    $conf[".Engine"] = "latex";

    // The invoice renderer uses absolute text blocks for the sender and
    // recipient envelope windows.  Declare the package through the runtime
    // configuration rather than relying on an auxiliary file being installed
    // next to the renderer.
    $extra_header = isset($conf[".LatexExtraHeader"]) && is_string($conf[".LatexExtraHeader"])
        ? rtrim($conf[".LatexExtraHeader"])."\n"
        : "";
    if (strpos($extra_header, "{textpos}") === false)
        $extra_header .= "\\usepackage[absolute,overlay]{textpos}\n";
    if (strpos($extra_header, "{colortbl}") === false)
        $extra_header .= "\\usepackage{colortbl}\n";
    $conf[".LatexExtraHeader"] = $extra_header;
    $invoice = isset($conf["Invoice"]) && is_array($conf["Invoice"]) ? $conf["Invoice"] : [];
    $school = isset($conf["School"]) && is_array($conf["School"]) ? $conf["School"] : [];
    $recipient = InvoiceFirstIdentity($conf);
    $student = isset($conf["Student"]) && is_array($conf["Student"]) ? $conf["Student"] : [];
    $payer = InvoiceFinancialResponsible($conf);
    $prepared = InvoicePrepare($conf);
    $currency = InvoiceScalar($invoice["Currency"] ?? "EUR", "EUR");
    $reference = trim(InvoiceScalar($invoice["Reference"] ?? ""));
    $date = trim(InvoiceScalar($invoice["IssueDateLabel"] ?? ($invoice["DateLabel"] ?? ($conf["Generation"]["Date"] ?? ""))));
    $due = trim(InvoiceScalar($invoice["DueDateLabel"] ?? ""));
    $paid_date = trim(InvoiceScalar($invoice["PaidDateLabel"] ?? ""));
    $type = trim(InvoiceScalar($invoice["TypeLabel"] ?? ""));
    $draft = (strcasecmp($reference, "BROUILLON") === 0 || strcasecmp($date, "BROUILLON") === 0);
    $student_identity = InvoiceIdentityLabel($student);
    $payer_identity = InvoiceIdentityLabel($payer);
    $status_label = trim(InvoiceScalar($invoice["StatusLabel"] ?? ""));
    if ($status_label === "")
    {
        if ($draft)
            $status_label = "Brouillon — non émise";
        else if ($paid_date !== "")
            $status_label = "Réglée le ".$paid_date;
        else
            $status_label = "Émise";
    }
    $billing = InvoiceBillingInformationParts($conf["BillingInformation"] ?? "");
    $vat_exemption = trim(InvoiceScalar($invoice["VATExemption"] ?? ""));
    $has_billing_information = ($billing["intro"] !== "" || $billing["details"] !== "" || $vat_exemption !== "");
    $layout = isset($conf["InvoiceLayout"]) && is_array($conf["InvoiceLayout"]) ? $conf["InvoiceLayout"] : [];

    // Keep the administrative invoice envelope-compatible with the Letter
    // renderer.  The defaults intentionally mirror res/docs/fr/.base_letter.
    $page_top = InvoiceDimension($layout, "TopMargin", "1cm");
    $page_bottom = InvoiceDimension($layout, "BottomMargin", "2cm");
    $header_height = InvoiceDimension($layout, "HeaderHeight", "1cm");
    $header_gap = InvoiceDimension($layout, "HeaderGap", "0.45cm");
    $footer_height = InvoiceDimension($layout, "FooterHeight", "2cm");
    $footer_rule_gap = InvoiceDimension($layout, "FooterRuleGap", "0.12cm");
    $from_x = InvoiceDimension($layout, "FromX", "2cm");
    $from_y = InvoiceDimension($layout, "FromY", "2.5cm");
    $from_width = InvoiceDimension($layout, "FromWidth", "7.5cm");
    $target_x = InvoiceDimension($layout, "TargetX", "11.5cm");
    $target_y = InvoiceDimension($layout, "TargetY", "5cm");
    $target_width = InvoiceDimension($layout, "TargetWidth", "7cm");
    // Keep the invoice metadata in the normal document flow, below the envelope
    // address zones and immediately above the administrative identity strip.
    $meta_width = InvoiceDimension($layout, "MetaWidth", "6.5cm");
    $body_gap = InvoiceDimension($layout, "BodyGap", "4cm");
    $payment_bottom_gap = InvoiceDimension($layout, "PaymentBottomGap", "0.45cm");
    $payment_body_gap = InvoiceDimension($layout, "PaymentBodyGap", "0.25cm");
    $school_block = InvoiceSchoolBlock($school);
    $recipient_block = InvoicePersonBlock($recipient);
    $has_meta = ($draft || $date !== "" || $due !== "" || $type !== "");
?>
---
geometry: left=2cm, right=2cm, top=<?=$page_top; ?>, bottom=<?=$page_bottom; ?>, headheight=<?=$header_height; ?>, headsep=<?=$header_gap; ?>, footskip=<?=$footer_height; ?>, includeheadfoot, paperwidth=21cm, paperheight=29.7cm
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
\setlength{\headheight}{<?=$header_height; ?>}
\setlength{\headsep}{<?=$header_gap; ?>}
\setlength{\footskip}{<?=$footer_height; ?>}
\renewcommand{\footruleskip}{<?=$footer_rule_gap; ?>}
\setlength{\parindent}{0pt}
\renewcommand{\arraystretch}{1.16}
<?php InvoiceBuildHeader($conf, $reference); ?>

<?php if ($has_billing_information) { ?>
\newsavebox{\invoicepaymentbox}
\savebox{\invoicepaymentbox}{%
\fcolorbox{black!30}{black!6}{%
\begin{minipage}[b]{0.965\textwidth}
\centering
\textbf{Informations de paiement}
<?php if ($vat_exemption !== "") { ?>

\smallskip
{\small <?=InvoiceLatexLines($vat_exemption); ?>}
<?php } ?>
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
\end{minipage}}%
}
\newlength{\invoicepaymentreserve}
\setlength{\invoicepaymentreserve}{\dimexpr\ht\invoicepaymentbox+\dp\invoicepaymentbox+<?=$payment_bottom_gap; ?>+<?=$payment_body_gap; ?>\relax}
\enlargethispage{-\invoicepaymentreserve}
\noindent\makebox[0pt][l]{%
  \raisebox{\dimexpr-\textheight+\topskip+<?=$payment_bottom_gap; ?>\relax}[0pt][0pt]{%
    \makebox[\textwidth][c]{\usebox{\invoicepaymentbox}}%
  }%
}\par\vspace{-\baselineskip}
<?php } ?>
<?php InvoiceBuildFooter($conf, $footer_height); ?>

<?php if ($school_block !== "") { ?>
\begin{textblock*}{<?=$from_width; ?>}(<?=$from_x; ?>,<?=$from_y; ?>)
\raggedright
<?=$school_block; ?>
\end{textblock*}
<?php } ?>

<?php if ($recipient_block !== "") { ?>
\begin{textblock*}{<?=$target_width; ?>}(<?=$target_x; ?>,<?=$target_y; ?>)
\raggedright
<?=$recipient_block; ?>
\end{textblock*}
<?php } ?>

\vspace*{<?=$body_gap; ?>}

<?php if ($has_meta) { ?>
\noindent
\fcolorbox{black!22}{black!2}{%
\begin{minipage}[t]{\dimexpr<?=$meta_width; ?>-2\fboxsep-2\fboxrule\relax}
\raggedright
{\small
Date d’émission : <?php if ($draft || $date === "") { ?>—<?php } else { ?><?=InvoiceLatex($date); ?><?php } ?>
<?php if ($due !== "") { ?>\\[0.04cm] Échéance : <?=InvoiceLatex($due); ?><?php } ?>
<?php if ($type !== "") { ?>\\[0.04cm] Facturation : <?=InvoiceLatex($type); ?><?php } ?>
}
\end{minipage}}
\par
\vspace{0.18cm}
<?php } ?>

\noindent
\arrayrulecolor{black!25}
\begin{tabularx}{\textwidth}{@{}>{\raggedright\arraybackslash}X@{\hspace{0.65cm}}>{\raggedright\arraybackslash}X@{\hspace{0.65cm}}>{\raggedright\arraybackslash}p{3.7cm}@{}}
\toprule
{\scriptsize\color{black!55} Élève / bénéficiaire} & {\scriptsize\color{black!55} Payeur / responsable financier} & {\scriptsize\color{black!55} Statut} \\[-0.04cm]
{\small\bfseries <?=InvoiceLatex($student_identity !== "" ? $student_identity : "—"); ?>} & {\small\bfseries <?=InvoiceLatex($payer_identity !== "" ? $payer_identity : "—"); ?>} & {\small <?=InvoiceLatex($status_label); ?>} \\
\bottomrule
\end{tabularx}
\arrayrulecolor{black}

\vspace{0.30cm}

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
<?php if (!empty($item["details"])) { ?>
\multicolumn{5}{@{}p{\textwidth}@{}}{{\scriptsize\color{black!58} <?=InvoiceLatex($item["details"]); ?>}} \\
<?php } ?>
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
<?php if (!empty($invoice["Note"])) { ?>
\vspace{0.45cm}

{\small <?=InvoiceLatexLines($invoice["Note"]); ?>}
<?php } ?>
<?php
}
