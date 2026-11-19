# Changelog

All notable changes to `nickdekruijk/shopwire` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.5.1] - 2026-11-19

### Fixed

- **A discount code is only accepted when the cart applies it.** The validation compared
  the minimum and maximum amount of a discount with the total amount, including shipping.
  The cart compares them with the products only. With 45 euro of products, 6 euro shipping
  and a code with a minimum of 50 euro, the code was accepted without an error but no
  discount was given. Both now use the products without shipping.

### Added

- **Dutch example discounts.** The `DiscountsDutch` seeder creates four example discounts:
  free shipping with and without a code, a percentage and a fixed amount. They are all
  inactive, and existing discounts are left alone.

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

- **Product variants.** With the new `product_option_model` and `product_option_columns`
  config options a product can have options, for example the sizes of a shirt. Two
  variants of the same product are separate items in the cart. The title becomes
  `Product (Variant)`, the price and weight of the variant are used and fall back to those
  of the product, and the order line stores the variant in `product_option_id`. Without
  `product_option_model` nothing changes.
- **Choosing a variant in the add to cart component.** The component shows a select with
  the active variants of a product and requires one to be chosen. A variant can also be
  passed in: `<livewire:shopwire-add :product="$product" :option="$option" />`.
- **Stock.** With the new `decrease_stock` config option, off by default, the stock of each
  ordered product is decreased once the order is paid. A variant with its own stock column
  has its own stock, which is decreased instead. Set `product_option_columns.stock` to
  `null` to keep one stock on the product, shared by its variants. A `null` stock means
  unlimited.
- **`prevent_oversell`.** With this config option, off by default, a customer can't put more
  in the cart than is in stock. The quantity is limited to the stock and the customer gets
  a message, in Dutch and English. The stock is checked again when the customer goes to the
  payment. Stock is not reserved, so two customers paying at the same time can still buy
  the last one and the stock can become negative.
- **Events.** `Events\OrderPaid` is dispatched exactly once per order, `Events\PaymentCreated`
  on every payment attempt.
- **The discount per VAT rate on the discount line.** The price of a discount line has a new
  `vat_shares` value with the `including`, `excluding` and `vat` amount per VAT rate. It is
  stored with the order, so the VAT per rate can be derived from an order afterwards, also
  when the discount covers more than one rate.
- `ShopwireProduct::shopwirePrice($option, $taxfree)` returns the price details of a product
  or one of its variants. `shopwire_price` and `shopwire_price_taxfree` still exist.
- `Shopwire::productOptions($product)` returns the active variants of a product.
- Each item in the checkout component has a `cart_item_id`, `product_id` and
  `product_option_id`, and each cart item from `CartController::getItems()` a
  `product_option_id`.
- **Tests.** A Pest and Testbench suite, and a GitHub Actions workflow for PHP 8.2 to 8.4,
  Laravel 12 and 13 and Livewire 3 and 4.

### Changed

- **Shops that show the VAT per rate, or the amount excluding VAT on an order with a
  discount, show different numbers.** The new numbers are the correct ones. Orders that
  already exist are not changed.
- The `vat_rate` of a discount line is the VAT rate when the discount covers one rate. It
  stays `null` when the discount covers more than one.
- **`CartController::add()` and `update()` can return `false`.** They always returned
  `true`. They now return `false` when the variant does not belong to the product or is
  not active (nothing is stored), and when `prevent_oversell` limited the quantity to the
  stock (the limited quantity is stored).
- **Shops that already had `product_option_model` in their config now get variants
  everywhere:** in the title, the weight, the order lines and the stock. Before, only the
  price looked at it.
- **In the checkout component, an item with a variant has the key `{product id}-{variant id}`**
  in `$items` and `$quantity`, for example `12-5`. An item without a variant keeps the
  product id as key, so published views keep working.
- The add to cart view shows the price from a new `$price` variable, the price of the chosen
  variant. Published views that use `$product->price` keep working and show the product price.
