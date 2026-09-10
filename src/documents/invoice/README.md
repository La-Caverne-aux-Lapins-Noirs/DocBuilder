# Invoice document

`Document = "Invoice"` is a data-driven invoice renderer. Repeated invoice
lines are interpreted in PHP by DocBuilder; the document source does not need
to generate a textual loop.

Canonical line input:

```dabsic
{Products
  [
    Content = "Frais de scolarité"
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
`PriceCents` or `UnitPriceCents` instead.

DocBuilder computes line HT totals in integer cents, groups taxable bases by
VAT rate, rounds VAT once per rate, then computes total HT, total VAT and total
TTC. The renderer accepts negative prices, which can be useful for discounts.

For compatibility with callers that still describe a one-line invoice,
`Invoice.Label`, `Invoice.Amount`/`AmountCents` and `Invoice.VAT` are accepted.
The manual document page can instead use `Invoice.Quantity` and
`Invoice.UnitPrice`.

Optional invoice metadata includes `Invoice.Reference`, `IssueDateLabel`,
`DueDateLabel`, `TypeLabel`, `Currency`, `VATExemption` and `Note`.
