# Invoice document

`Document = "Invoice"` is a data-driven invoice renderer. Repeated invoice
lines are interpreted in PHP by DocBuilder; the document source does not need
to generate a textual loop.

Canonical line input:

```dabsic
{Products
  [
    Content = "Frais de scolarité"
    Details = "Déjà payé : 2 000,00 € — Reste après cette facture : 4 000,00 €"
    Quantity = 1
    Price = 1000.00
    VAT = 0
  ]
  [
    Content = "Licence logicielle"
    Quantity = 2
    Price = 25.50
    VAT = 20
  ]
}
```

`Items` is accepted as an alias for `Products`. `Price` is the unit price
excluding VAT. Callers that already use integer money may provide
`PriceCents` or `UnitPriceCents` instead. `Details` (aliases: `LineDetails`,
`Note`) adds a subdued full-width subline immediately below that invoice item.
For the one-line compatibility path, `Invoice.LineDetails` or
`Invoice.InstallmentSummary` provides the same subline.

DocBuilder computes line HT totals in integer cents, groups taxable bases by
VAT rate, rounds VAT once per rate, then computes total HT, total VAT and total
TTC. The renderer accepts negative prices, which can be useful for discounts.

For compatibility with callers that still describe a one-line invoice,
`Invoice.Label`, `Invoice.Amount`/`AmountCents` and `Invoice.VAT` are accepted.
The manual document page can instead use `Invoice.Quantity` and
`Invoice.UnitPrice`.

Optional invoice metadata includes `Invoice.Reference`, `IssueDateLabel`,
`DueDateLabel`, `PaidDateLabel`, `StatusLabel`, `TypeLabel`, `Currency`,
`VATExemption` and `Note`.  When `Student` and `Finance` (or
`FinancialResponsible`) are available, the rendered invoice explicitly labels
the beneficiary and payer in a compact administrative reference strip. A
`BROUILLON` reference is shown as “Brouillon — non émise” rather than as a
fictional issue date. `PaidDateLabel`, when present, is rendered as “Réglée le
…”.

The invoice layout can be tuned with an optional `InvoiceLayout` block. Its
administrative defaults intentionally mirror the Letter renderer so invoices
fit the same window-envelope geometry: a 1 cm header, sender at `(2cm, 2.5cm)`,
recipient at `(11.5cm, 5cm)`, and a 4 cm body gap. Available dimensions include
`TopMargin`, `BottomMargin`, `HeaderHeight`, `HeaderGap`, `FooterHeight`,
`FooterRuleGap`, `FromX`, `FromY`, `FromWidth`, `TargetX`, `TargetY`,
`TargetWidth`, `MetaWidth` and `BodyGap`. The invoice metadata (issue date, due
date and billing type) is rendered in the normal body flow, left-aligned,
immediately above the beneficiary/payer/status strip. Keeping it out of the
absolute sender/recipient area prevents it from appearing through a window
envelope. String invoice footers are centered across the full text width; array
footers keep their explicit left/center/right slots.

When `BillingInformation` is present, the payment-information box is anchored
to the bottom of the first page, immediately above the regular footer. The
first page reserves the measured height of that box, so a long `Products`
table continues on the next page instead of colliding with payment details.
`PaymentBottomGap` controls how far the box is raised above the bottom of the
normal text area (default `0.45cm`) and `PaymentBodyGap` adds clearance between
the flowing invoice body and the reserved payment zone (default `0.25cm`).
Subsequent pages keep the normal full invoice body height.

The fixed sender/recipient envelope blocks use LaTeX `textpos`. The renderer's
`extra-header.tex` loads that package through DocBuilder's optional per-document
extra header; this is necessary because `Compile.php` already supplies
`configuration.tex` with Pandoc's `--include-in-header`, which otherwise
overrides `header-includes` emitted in the document YAML.
