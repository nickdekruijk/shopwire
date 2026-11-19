<?php

use Illuminate\Support\Facades\Validator;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;
use NickDeKruijk\Shopwire\Models\Discount;
use NickDeKruijk\Shopwire\Models\ShippingRate;
use NickDeKruijk\Shopwire\Models\Vat;
use NickDeKruijk\Shopwire\Rules\DiscountCodeValid;
use NickDeKruijk\Shopwire\Seeds\DiscountsDutch;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;

/**
 * A cart with one product of the given price including 21% VAT, and shipping of 6.05.
 */
function cartWithProductOf(string $price): void
{
    $vat = new Vat;
    $vat->forceFill(['rate' => 21, 'included' => true])->save();
    $product = Product::create(['title' => 'Shirt', 'price' => $price, 'vat_id' => $vat->id]);
    (new ShippingRate)->forceFill(['title' => 'Pakket', 'rate' => '6.05', 'vat_included' => true])->save();

    $cart = new Cart;
    $cart->forceFill(['session_id' => 'test', 'country_code' => 'NL'])->save();
    (new CartItem)->forceFill(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1])->save();
    Shopwire::session(['cart_id' => $cart->id]);
}

function discountCodeAccepted(string $code): bool
{
    return Validator::make(['code' => $code], ['code' => [new DiscountCodeValid]])->passes();
}

function discountApplied(string $code): bool
{
    return CartController::getItems($code)->statistics['has_discount_applied'];
}

it('refuses a discount code when only the shipping brings the cart above the minimum amount', function () {
    cartWithProductOf('45.00');
    (new Discount)->forceFill(['title' => 'Korting', 'active' => true, 'discount_code' => 'VIJFTIG', 'amount_min' => 50, 'discount_abs' => 5])->save();

    // 45.00 of products and 6.05 shipping: 51.05 in total, but the minimum is about the products
    expect(discountCodeAccepted('VIJFTIG'))->toBeFalse()
        ->and(discountApplied('VIJFTIG'))->toBeFalse();
});

it('accepts a discount code when the products are above the minimum amount', function () {
    cartWithProductOf('55.00');
    (new Discount)->forceFill(['title' => 'Korting', 'active' => true, 'discount_code' => 'VIJFTIG', 'amount_min' => 50, 'discount_abs' => 5])->save();

    expect(discountCodeAccepted('VIJFTIG'))->toBeTrue()
        ->and(discountApplied('VIJFTIG'))->toBeTrue()
        ->and(discountCodeAccepted('ONBEKEND'))->toBeFalse();
});

it('accepts a discount code when only the shipping brings the cart above the maximum amount', function () {
    cartWithProductOf('45.00');
    (new Discount)->forceFill(['title' => 'Korting', 'active' => true, 'discount_code' => 'KLEIN', 'amount_max' => 50, 'discount_abs' => 5])->save();

    expect(discountCodeAccepted('KLEIN'))->toBeTrue()
        ->and(discountApplied('KLEIN'))->toBeTrue();
});

it('seeds the Dutch example discounts, all inactive', function () {
    $this->seed(DiscountsDutch::class);

    expect(Discount::count())->toBe(4)
        ->and(Discount::where('active', true)->count())->toBe(0)
        ->and(Discount::pluck('discount_code')->all())->toBe([null, 'VERZENDKOSTEN0', 'KORTING10', 'KORTING15']);
});

it('leaves existing discounts alone when seeding', function () {
    (new Discount)->forceFill(['title' => 'Eigen korting', 'active' => true, 'discount_perc' => 20])->save();
    (new Discount)->forceFill(['title' => 'Gratis verzending boven 100 euro', 'active' => true, 'amount_min' => 75, 'free_shipping' => true])->save();

    $this->seed(DiscountsDutch::class);
    $this->seed(DiscountsDutch::class);

    $existing = Discount::where('title', 'Gratis verzending boven 100 euro')->get();

    // Not truncated, not duplicated and not overwritten
    expect(Discount::count())->toBe(5)
        ->and(Discount::where('title', 'Eigen korting')->exists())->toBeTrue()
        ->and($existing)->toHaveCount(1)
        ->and($existing[0]->active)->toBeTrue()
        ->and((float) $existing[0]->amount_min)->toBe(75.0);
});

it('applies a seeded discount once it is activated', function () {
    cartWithProductOf('121.00');
    $this->seed(DiscountsDutch::class);

    expect(discountCodeAccepted('KORTING10'))->toBeFalse();

    Discount::where('discount_code', 'KORTING10')->update(['active' => true]);
    $cart = CartController::getItems('KORTING10');

    // 10% of the products, not of the shipping: 121.00 - 12.10 + 6.05
    expect(discountCodeAccepted('KORTING10'))->toBeTrue()
        ->and($cart->statistics['amount_including_vat'])->toBe(114.95)
        ->and($cart->items->firstWhere('type', 'discount')->price->price_including_vat)->toBe(-12.1);
});
