<?php
require_once (__DIR__."/../src/documents/invoice/BuildDocument.php");

function invoice_expect($condition, $message)
{
    if (!$condition)
        throw new RuntimeException($message);
}

$prepared = InvoicePrepare([
    "Products" => [
        ["Content" => "A", "Quantity" => 2, "Price" => 100, "VAT" => 20],
        ["Content" => "B", "Quantity" => 1, "Price" => "50,00 €", "VAT" => "5,5"],
    ],
]);
invoice_expect($prepared["total_ht"] === 25000, "HT total");
invoice_expect($prepared["total_vat"] === 4275, "VAT total");
invoice_expect($prepared["total_ttc"] === 29275, "TTC total");
invoice_expect(count($prepared["vat_groups"]) === 2, "VAT groups");

$single = InvoicePrepare([
    "Products" => ["Content" => "Single", "Quantity" => "1,5", "PriceCents" => 1000, "VAT" => 20],
]);
invoice_expect(count($single["items"]) === 1, "single associative item normalization");
invoice_expect($single["total_ht"] === 1500, "fractional quantity");
invoice_expect($single["total_vat"] === 300, "fractional quantity VAT");

$fallback = InvoicePrepare([
    "Invoice" => [
        "Label" => "Manual invoice",
        "Amount" => "1 234,56 €",
        "VAT" => 0,
        "LineDetails" => "Déjà payé : 100,00 € — Reste : 900,00 €",
    ],
]);
invoice_expect($fallback["total_ht"] === 123456, "Invoice fallback amount");
invoice_expect($fallback["total_ttc"] === 123456, "Invoice fallback total");
invoice_expect($fallback["items"][0]["details"] !== "", "Invoice fallback line details");

$manual = InvoicePrepare([
    "Invoice" => ["Label" => "Manual line", "Quantity" => 2, "UnitPrice" => "10,00", "VAT" => 20],
]);
invoice_expect($manual["total_ht"] === 2000, "manual unit price");
invoice_expect($manual["total_vat"] === 400, "manual unit price VAT");

$rounding = InvoicePrepare([
    "Products" => [
        ["Content" => "Rounding", "Quantity" => 3, "Price" => "0,01", "VAT" => "5,5"],
    ],
]);
invoice_expect($rounding["total_ht"] === 3, "cent arithmetic");
invoice_expect($rounding["total_vat"] === 0, "VAT group rounding");

echo "invoice tests: ok\n";

$group_rounding = InvoicePrepare([
    "Products" => [
        ["Content" => "A", "Price" => "0,01", "VAT" => 20],
        ["Content" => "B", "Price" => "0,01", "VAT" => 20],
        ["Content" => "C", "Price" => "0,01", "VAT" => 20],
    ],
]);
invoice_expect($group_rounding["total_ht"] === 3, "VAT group base");
invoice_expect($group_rounding["total_vat"] === 1, "VAT rounded after rate aggregation");

echo "invoice VAT grouping: ok\n";


$render_conf = [
    "Invoice" => ["Reference" => "TEST-001", "IssueDateLabel" => "13/09/2026", "PaidDateLabel" => "15/09/2026", "VATExemption" => "TVA non applicable — test"],
    "InvoiceLayout" => [
        "TopMargin" => "1cm",
        "BottomMargin" => "2cm",
        "HeaderHeight" => "1cm",
        "FromX" => "2cm",
        "FromY" => "2.5cm",
        "TargetX" => "11.5cm",
        "TargetY" => "5cm",
        "MetaWidth" => "6.5cm",
        "BodyGap" => "4cm",
        "PaymentBottomGap" => "0.45cm",
    ],
    "Header" => ["Right" => "LOGO"],
    "Footer" => "Footer text",
    "School" => ["LegalName" => "School", "LegalAddress" => "1 rue School"],
    "Student" => ["Identity" => "Student", "Address" => "2 rue Student"],
    "Finance" => ["Identity" => "Payer", "Address" => "3 rue Payer"],
    "Products" => [["Content" => "Layout test", "Quantity" => 1, "Price" => 100, "VAT" => 0]],
    "BillingInformation" => "RIB :\nIBAN : FR76 0000 0000 0000",
];
ob_start();
BuildDocument($render_conf);
$rendered = ob_get_clean();
invoice_expect(isset($render_conf[".LatexExtraHeader"]), "invoice declares renderer LaTeX header");
invoice_expect(strpos($render_conf[".LatexExtraHeader"], "\\usepackage[absolute,overlay]{textpos}") !== false, "invoice declares textpos package");
invoice_expect(strpos($render_conf[".LatexExtraHeader"], "\\usepackage{colortbl}") !== false, "invoice declares colortbl package");
invoice_expect(strpos($rendered, "top=1cm, bottom=2cm, headheight=1cm") !== false, "invoice uses letter-like page geometry");
invoice_expect(strpos($rendered, "\\fancyhead[L]{{\\small\\bfseries Facture n\\textdegree{} TEST-001}}") !== false, "compact invoice heading");
invoice_expect(strpos($rendered, "\\begin{textblock*}{7.5cm}(2cm,2.5cm)") !== false, "sender uses envelope position");
invoice_expect(strpos($rendered, "\\begin{textblock*}{7cm}(11.5cm,5cm)") !== false, "recipient uses envelope position");
invoice_expect(strpos($rendered, "\\begin{textblock*}{6.5cm}(12.5cm,2.5cm)") === false, "invoice metadata does not use the envelope address area");
invoice_expect(strpos($rendered, "Date d’émission : 13/09/2026") !== false, "issued invoice date is shown in metadata box");
invoice_expect(strpos($rendered, "\\phantom{\\usebox{\\invoicemetabox}}") === false, "metadata no longer needs an absolute-position placeholder");
invoice_expect(strpos($rendered, "\\vspace*{4cm}") !== false, "body starts after envelope zones");
$metadata_position = strpos($rendered, "Date d’émission : 13/09/2026");
$identity_strip_position = strpos($rendered, "Élève / bénéficiaire");
invoice_expect($metadata_position !== false && $identity_strip_position !== false && $metadata_position < $identity_strip_position, "invoice metadata is immediately above the identity/status strip");
invoice_expect(strpos($rendered, "\\begin{minipage}[t]{\\dimexpr6.5cm-2\\fboxsep-2\\fboxrule\\relax}\n\\raggedright") !== false, "invoice metadata text is left aligned");
invoice_expect(strpos($rendered, "\\end{minipage}}%\n\\par") === false, "metadata box does not leak a literal percent through Pandoc");
invoice_expect(strpos($rendered, "\\end{minipage}}\n\\par") !== false, "metadata box closes cleanly before the identity strip");
invoice_expect(strpos($rendered, "\\centering\n  Footer text") !== false, "string invoice footer text is centered");
invoice_expect(strpos($rendered, "\\raggedright Footer text") === false, "string invoice footer is no longer left aligned");
invoice_expect(strpos($rendered, "\\newsavebox{\\invoicepaymentbox}") !== false, "payment box is pre-rendered");
invoice_expect(strpos($rendered, "\\enlargethispage{-\\invoicepaymentreserve}") !== false, "first page reserves payment space");
invoice_expect(strpos($rendered, "-\\textheight+\\topskip+0.45cm") !== false, "payment box is raised above footer");
invoice_expect(substr_count($rendered, "Informations de paiement") === 1, "payment block rendered once");
invoice_expect(substr_count($rendered, "TVA non applicable — test") === 1, "VAT exemption is rendered once in payment block");
invoice_expect(strpos($rendered, "Élève / bénéficiaire") !== false, "student role is explicit");
invoice_expect(strpos($rendered, "Payeur / responsable financier") !== false, "payer role is explicit");
invoice_expect(strpos($rendered, "{\\arrayrulecolor") === false, "status band is not wrapped in a Pandoc-escaped brace group");
invoice_expect(strpos($rendered, "Student") !== false && strpos($rendered, "Payer") !== false, "student and payer identities are rendered");
invoice_expect(strpos($rendered, "Réglée le 15/09/2026") !== false, "paid date becomes invoice status when available");

$details_conf = $render_conf;
$details_conf["Products"] = [[
    "Content" => "Échéance 2/4",
    "Details" => "Déjà payé sur la scolarité : 2 000,00 € — Montant total de la scolarité (hors frais d'inscription) : 8 000,00 € — Reste à payer après cette facture : 4 000,00 €",
    "Quantity" => 1,
    "Price" => 2000,
    "VAT" => 0,
]];
ob_start();
BuildDocument($details_conf);
$details_rendered = ob_get_clean();
invoice_expect(strpos($details_rendered, "\\multicolumn{5}{@{}p{\\textwidth}@{}}") !== false, "line details span the invoice item row");
invoice_expect(strpos($details_rendered, "Déjà payé sur la scolarité") !== false, "installment summary is rendered under the item");

$draft_conf = $render_conf;
$draft_conf["Invoice"] = ["Reference" => "BROUILLON", "IssueDateLabel" => "BROUILLON", "DueDateLabel" => "21/09/2026", "TypeLabel" => "École"];
ob_start();
BuildDocument($draft_conf);
$draft_rendered = ob_get_clean();
invoice_expect(strpos($draft_rendered, "Brouillon — non émise") !== false, "draft status is explicit");
invoice_expect(strpos($draft_rendered, "Émise le BROUILLON") === false, "draft is not presented as an issue date");
invoice_expect(strpos($draft_rendered, "Date d’émission : —") !== false, "draft keeps an explicit empty issue-date field");
invoice_expect(strpos($draft_rendered, "Facturation : École") !== false, "invoice type is labelled");

$without_billing = $render_conf;
unset($without_billing["BillingInformation"]);
unset($without_billing["Invoice"]["VATExemption"]);
ob_start();
BuildDocument($without_billing);
$without_billing_rendered = ob_get_clean();
invoice_expect(strpos($without_billing_rendered, "invoicepaymentbox") === false, "no payment reservation without billing information");

echo "invoice envelope/payment layout: ok\n";
