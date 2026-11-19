# Changelog

All notable changes to `nickdekruijk/shopwire` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.5.0] - 2026-11-19

### Fixed

- **An order is handled only once when the verify redirect and the webhook arrive at the
  same time.** Both requests saw an unpaid order and both sent the order mails. The order
  is now marked as paid with one atomic query, and only the request that flips the flag
  sends the mails, decreases the stock and dispatches the event.
- **A failing mail no longer leaves a paid order unpaid.** When sending the order mails
  failed, the order stayed unpaid and the payment provider kept retrying the webhook. The
  order now stays paid, and the customer mail, the owner mail and the stock decrease are
  each guarded on their own: a failing step is reported and logged, the steps after it
  still run.
- **Totals no longer drift by a fraction of a cent.** Cart, VAT, shipping, discount and
  order totals are calculated in whole cents by the new `Support\Money` helper. Amounts are
  still stored and exposed as decimals. This fixes totals like `0.30000000000000004` and
  one cent VAT differences.
- **The VAT per rate adds up every product.** `amount_vat` only counted the last product of
  each rate because of a typo.
- **A discount lowers the amount excluding VAT and the VAT.** Discounts only lowered the
  total including VAT. They are now spread over the VAT rates they apply to (the items, or
  the items and shipping with `apply_to_shipping`), proportional to the amount of each
  rate. Any rounding remainder goes to the largest rate, and a 100% discount brings every
  amount to exactly zero.
- **The amount is always sent to Mollie as a string with exactly two decimals.**

### Added

- **Stock.** With the new `decrease_stock` config option, off by default, the stock of each
  ordered product is decreased once the order is paid. A `null` stock means unlimited.
  Stock is not checked during checkout, so it can become negative.
- **Events.** `Events\OrderPaid` is dispatched exactly once per order, `Events\PaymentCreated`
  on every payment attempt.
- **The discount per VAT rate on the discount line.** The price of a discount line has a new
  `vat_shares` value with the `including`, `excluding` and `vat` amount per VAT rate. It is
  stored with the order, so the VAT per rate can be derived from an order afterwards, also
  when the discount covers more than one rate.
- **Tests.** A Pest and Testbench suite, and a GitHub Actions workflow for PHP 8.2 to 8.4,
  Laravel 12 and 13 and Livewire 3 and 4.

### Changed

- **Shops that show the VAT per rate, or the amount excluding VAT on an order with a
  discount, show different numbers.** The new numbers are the correct ones. Orders that
  already exist are not changed.
- The `vat_rate` of a discount line is the VAT rate when the discount covers one rate. It
  stays `null` when the discount covers more than one.
