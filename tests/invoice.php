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
    "Invoice" => ["Label" => "Manual invoice", "Amount" => "1 234,56 €", "VAT" => 0],
]);
invoice_expect($fallback["total_ht"] === 123456, "Invoice fallback amount");
invoice_expect($fallback["total_ttc"] === 123456, "Invoice fallback total");

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
