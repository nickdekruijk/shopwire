<?php

use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Livewire\AddToCart;
use NickDeKruijk\Shopwire\Livewire\Checkout;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Models\OrderLine;
use NickDeKruijk\Shopwire\Models\ShippingRate;
use NickDeKruijk\Shopwire\Models\Vat;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Tests\Fixtures\FakePaymentProvider;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;
use NickDeKruijk\Shopwire\Tests\Fixtures\ProductOption;

/**
 * A shirt of 24.20 including 21% VAT with the sizes S and L, and an empty cart.
 *
 * @return array{Product, ProductOption, ProductOption}
 */
function shirtWithSizes(array $shirt = [], array $small = [], array $large = []): array
{
    $vat = new Vat;
    $vat->forceFill(['rate' => 21, 'included' => true])->save();

    $shirt = Product::create(array_merge(['title' => 'Shirt', 'price' => '24.20', 'vat_id' => $vat->id, 'weight' => 200], $shirt));
    $small = ProductOption::create(array_merge(['product_id' => $shirt->id, 'title' => 'S', 'sort' => 1], $small));
    $large = ProductOption::create(array_merge(['product_id' => $shirt->id, 'title' => 'L', 'sort' => 2], $large));

    $cart = new Cart;
    $cart->forceFill(['session_id' => 'test', 'country_code' => 'NL'])->save();
    Shopwire::session(['cart_id' => $cart->id]);

    return [$shirt, $small, $large];
}

/**
 * The product lines of the cart, without the shipping line.
 */
function cartLines()
{
    return CartController::getItems()->items->where('product_id', '>', 0)->values();
}

/**
 * Everything the checkout needs to go to the payment.
 */
function readyForCheckout(): void
{
    (new ShippingRate)->forceFill(['title' => 'Pakket', 'rate' => '6.05', 'vat_included' => true])->save();

    Shopwire::session([
        'checkout_account' => 'guest',
        'checkout_form' => [
            'email' => 'klant@example.com',
            'firstname' => 'Jan',
            'lastname' => 'Jansen',
            'address' => 'Dorpsstraat 1',
            'postcode' => '1234 AB',
            'city' => 'Utrecht',
            'agree_with_terms' => true,
        ],
    ]);
}

beforeEach(function () {
    config(['shopwire.product_option_model' => ProductOption::class]);
    FakePaymentProvider::$paid = true;
    Mail::fake();
});

it('keeps two options of the same product apart in the cart', function () {
    [$shirt, $small, $large] = shirtWithSizes();

    expect(CartController::add($shirt->id, 1, $small->id))->toBeTrue()
        ->and(CartController::add($shirt->id, 2, $large->id))->toBeTrue()
        ->and(CartController::add($shirt->id, 1, $large->id))->toBeTrue();

    $lines = cartLines();
    $statistics = CartController::getItems()->statistics;

    expect($lines)->toHaveCount(2)
        ->and($lines[0]->title)->toBe('Shirt (S)')
        ->and($lines[0]->product_option_id)->toEqual($small->id)
        ->and($lines[0]->quantity)->toEqual(1)
        ->and($lines[1]->title)->toBe('Shirt (L)')
        ->and($lines[1]->product_option_id)->toEqual($large->id)
        ->and($lines[1]->quantity)->toEqual(3)
        ->and($statistics['count'])->toEqual(4)
        ->and($statistics['count_unique'])->toBe(2);
});

it('uses the price of the option and falls back to the price of the product', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['price' => '29.04']);
    CartController::add($shirt->id, 1, $small->id);
    CartController::add($shirt->id, 2, $large->id);

    $lines = cartLines();
    $statistics = CartController::getItems()->statistics;

    expect((float) $lines[0]->price->price_including_vat)->toBe(24.2)
        ->and($lines[0]->price->price_excluding_vat)->toBe(20.0)
        ->and((float) $lines[1]->price->price_including_vat)->toBe(29.04)
        ->and($lines[1]->price->price_excluding_vat)->toBe(24.0)
        ->and($statistics['amount_including_vat'])->toBe(82.28)
        ->and($statistics['amount_excluding_vat'])->toBe(68.0)
        ->and(array_values($statistics['amount_vat']))->toBe([14.28]);
});

it('does not leak the price of one option to another item of the same product', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['price' => '29.04']);
    CartController::add($shirt->id, 1, $large->id);
    CartController::add($shirt->id, 1, $small->id);
    CartController::add($shirt->id, 1);

    $prices = cartLines()->map(fn($line) => (float) $line->price->price_including_vat)->all();

    expect($prices)->toBe([29.04, 24.2, 24.2]);
});

it('uses the weight of the option and falls back to the weight of the product', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['weight' => 350]);
    CartController::add($shirt->id, 1, $small->id);
    CartController::add($shirt->id, 2, $large->id);

    $lines = cartLines();

    expect((float) $lines[0]->weight)->toBe(200.0)
        ->and((float) $lines[1]->weight)->toBe(350.0)
        ->and((float) CartController::getItems()->statistics['weight'])->toBe(900.0);
});

it('refuses an option of another product and an inactive option', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['active' => false]);
    $cap = Product::create(['title' => 'Cap', 'price' => '10.00', 'vat_id' => $shirt->vat_id]);
    $cap_option = ProductOption::create(['product_id' => $cap->id, 'title' => 'One size']);

    expect(CartController::add($shirt->id, 1, $cap_option->id))->toBeFalse()
        ->and(CartController::add($shirt->id, 1, $large->id))->toBeFalse()
        ->and(CartController::add($shirt->id, 1, 999))->toBeFalse()
        ->and(CartItem::count())->toBe(0);
});

it('does not check the stock by default', function () {
    [$shirt, $small] = shirtWithSizes(small: ['stock' => 2]);

    expect(CartController::add($shirt->id, 5, $small->id))->toBeTrue()
        ->and(cartLines()[0]->quantity)->toEqual(5);
});

it('limits the quantity to the stock of the option with prevent_oversell', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small, $large] = shirtWithSizes(shirt: ['stock' => 1], small: ['stock' => 2], large: ['stock' => null]);

    expect(CartController::add($shirt->id, 5, $small->id))->toBeFalse()
        ->and(CartController::add($shirt->id, 5, $large->id))->toBeTrue();

    $lines = cartLines();

    // S is limited to its own stock, L has no stock value and is unlimited. The stock of the shirt itself is not used.
    expect($lines[0]->quantity)->toEqual(2)
        ->and($lines[1]->quantity)->toEqual(5);
});

it('limits adding more of an option that is already in the cart', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small] = shirtWithSizes(small: ['stock' => 3]);

    expect(CartController::add($shirt->id, 2, $small->id))->toBeTrue()
        ->and(CartController::add($shirt->id, 2, $small->id))->toBeFalse()
        ->and(cartLines()[0]->quantity)->toEqual(3);
});

it('does not add an option that is sold out', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small] = shirtWithSizes(small: ['stock' => 0]);

    expect(CartController::add($shirt->id, 1, $small->id))->toBeFalse()
        ->and(CartItem::count())->toBe(0);
});

it('shares the stock of the product between its options when options have no stock column', function () {
    config(['shopwire.prevent_oversell' => true, 'shopwire.product_option_columns.stock' => null]);
    [$shirt, $small, $large] = shirtWithSizes(shirt: ['stock' => 4]);

    expect(CartController::add($shirt->id, 3, $small->id))->toBeTrue()
        ->and(CartController::add($shirt->id, 3, $large->id))->toBeFalse();

    $lines = cartLines();

    expect($lines[0]->quantity)->toEqual(3)
        ->and($lines[1]->quantity)->toEqual(1);
});

it('limits a product without options to its stock with prevent_oversell', function () {
    config(['shopwire.prevent_oversell' => true, 'shopwire.product_option_model' => null]);
    [$shirt] = shirtWithSizes(shirt: ['stock' => 2]);

    expect(CartController::add($shirt->id, 5))->toBeFalse()
        ->and(cartLines()[0]->quantity)->toEqual(2);
});

it('decreases the stock of the option after payment', function () {
    config(['shopwire.decrease_stock' => true]);
    [$shirt, $small, $large] = shirtWithSizes(shirt: ['stock' => 50], small: ['stock' => 10], large: ['stock' => 10]);

    $order = new Order;
    $order->forceFill([
        'payment_id' => 'tr_variants',
        'amount' => 10,
        'customer' => ['email' => 'klant@example.com'],
        'products' => [],
    ])->save();
    (new OrderLine)->forceFill(['order_id' => $order->id, 'product_id' => $shirt->id, 'product_option_id' => $small->id, 'title' => 'Shirt (S)', 'quantity' => 3, 'price' => 24.2])->save();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id])->assertOk();

    expect((float) $small->fresh()->stock)->toBe(7.0)
        ->and((float) $large->fresh()->stock)->toBe(10.0)
        ->and((float) $shirt->fresh()->stock)->toBe(50.0);
});

it('decreases the stock of the product after payment when options have no stock column', function () {
    config(['shopwire.decrease_stock' => true, 'shopwire.product_option_columns.stock' => null]);
    [$shirt, $small] = shirtWithSizes(shirt: ['stock' => 50], small: ['stock' => 10]);

    $order = new Order;
    $order->forceFill([
        'payment_id' => 'tr_variants',
        'amount' => 10,
        'customer' => ['email' => 'klant@example.com'],
        'products' => [],
    ])->save();
    (new OrderLine)->forceFill(['order_id' => $order->id, 'product_id' => $shirt->id, 'product_option_id' => $small->id, 'title' => 'Shirt (S)', 'quantity' => 3, 'price' => 24.2])->save();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id])->assertOk();

    expect((float) $shirt->fresh()->stock)->toBe(47.0)
        ->and((float) $small->fresh()->stock)->toBe(10.0);
});

it('requires an option in the add to cart component when the product has options', function () {
    [$shirt] = shirtWithSizes();

    Livewire::test(AddToCart::class, ['product' => $shirt])
        ->assertSee('Select a variant')
        ->call('add')
        ->assertHasErrors('option_id');

    expect(CartItem::count())->toBe(0);
});

it('adds the chosen option with the add to cart component', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['price' => '29.04']);

    Livewire::test(AddToCart::class, ['product' => $shirt])
        ->assertSee('24.20')
        ->set('option_id', $large->id)
        ->assertSee('29.04')
        ->set('quantity', 2)
        ->call('add')
        ->assertHasNoErrors()
        ->assertSet('cart_quantity', 2);

    $lines = cartLines();

    expect($lines)->toHaveCount(1)
        ->and($lines[0]->title)->toBe('Shirt (L)')
        ->and($lines[0]->quantity)->toEqual(2);
});

it('shows the quantity in the cart of the chosen option', function () {
    [$shirt, $small, $large] = shirtWithSizes();
    CartController::add($shirt->id, 3, $small->id);

    Livewire::test(AddToCart::class, ['product' => $shirt])
        ->assertSet('cart_quantity', 0)
        ->set('option_id', $small->id)
        ->assertSet('cart_quantity', 3)
        ->assertSet('quantity', 3)
        ->set('option_id', $large->id)
        ->assertSet('cart_quantity', 0)
        ->assertSet('quantity', 1);
});

it('accepts a fixed option in the add to cart component', function () {
    [$shirt, $small] = shirtWithSizes();

    Livewire::test(AddToCart::class, ['product' => $shirt, 'option' => $small])
        ->assertSet('option_fixed', true)
        ->assertDontSee('Select a variant')
        ->call('add')
        ->assertHasNoErrors();

    expect(cartLines()[0]->title)->toBe('Shirt (S)');
});

it('refuses an option of another product in the add to cart component', function () {
    [$shirt] = shirtWithSizes();
    $cap = Product::create(['title' => 'Cap', 'price' => '10.00', 'vat_id' => $shirt->vat_id]);
    $cap_option = ProductOption::create(['product_id' => $cap->id, 'title' => 'One size']);

    Livewire::test(AddToCart::class, ['product' => $shirt])
        ->set('option_id', $cap_option->id)
        ->call('add')
        ->assertHasErrors('option_id');

    expect(CartItem::count())->toBe(0);
});

it('tells the customer when the quantity is limited to the stock', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small, $large] = shirtWithSizes(small: ['stock' => 2], large: ['stock' => 0]);

    Livewire::test(AddToCart::class, ['product' => $shirt])
        ->assertSee('L (Sold out)')
        ->set('option_id', $small->id)
        ->set('quantity', 5)
        ->call('add')
        ->assertHasErrors('quantity')
        ->assertSee('Only 2 left in stock.')
        ->assertSet('quantity', 2)
        ->assertSet('cart_quantity', 2);

    expect(cartLines()[0]->quantity)->toEqual(2);
});

it('adds a product without options as before when options are configured', function () {
    [$shirt] = shirtWithSizes();
    $cap = Product::create(['title' => 'Cap', 'price' => '10.00', 'vat_id' => $shirt->vat_id]);

    Livewire::test(AddToCart::class, ['product' => $cap])
        ->assertDontSee('Select a variant')
        ->call('add')
        ->assertHasNoErrors()
        ->assertSet('cart_quantity', 1);

    expect(cartLines()[0]->title)->toBe('Cap');
});

it('changes the quantity of one option in the checkout without touching the other', function () {
    [$shirt, $small, $large] = shirtWithSizes();
    $cap = Product::create(['title' => 'Cap', 'price' => '10.00', 'vat_id' => $shirt->vat_id]);
    CartController::add($shirt->id, 1, $small->id);
    CartController::add($shirt->id, 1, $large->id);
    CartController::add($cap->id, 1);
    readyForCheckout();

    $component = Livewire::test(Checkout::class)
        ->assertSee('Shirt (S)')
        ->assertSee('Shirt (L)')
        ->set('quantity.' . $shirt->id . '-' . $small->id, 3)
        ->set('quantity.' . $cap->id, 2);

    // An item without an option keeps the product id as key, an item with an option gets the option id added
    expect(array_column($component->get('items'), 'id'))->toBe([$shirt->id . '-' . $small->id, $shirt->id . '-' . $large->id, $cap->id, null]);

    $quantities = cartLines()->map(fn($line) => $line->title . ': ' . $line->quantity)->all();

    expect($quantities)->toBe(['Shirt (S): 3', 'Shirt (L): 1', 'Cap: 2']);
});

it('stores the option on the order lines', function () {
    [$shirt, $small, $large] = shirtWithSizes(large: ['price' => '29.04']);
    CartController::add($shirt->id, 1, $small->id);
    CartController::add($shirt->id, 2, $large->id);
    readyForCheckout();

    Livewire::test(Checkout::class)
        ->call('gotoPayment')
        ->assertHasNoErrors()
        ->assertRedirect('https://example.com/pay');

    $order = Order::firstOrFail();
    $lines = $order->lines()->where('product_id', $shirt->id)->orderBy('id')->get();

    expect($lines)->toHaveCount(2)
        ->and($lines[0]->title)->toBe('Shirt (S)')
        ->and($lines[0]->product_option_id)->toEqual($small->id)
        ->and($lines[1]->title)->toBe('Shirt (L)')
        ->and($lines[1]->product_option_id)->toEqual($large->id)
        ->and((float) $lines[1]->price)->toBe(29.04)
        ->and($order->products[1]['product_option_id'])->toEqual($large->id)
        ->and($order->products[1])->not->toHaveKey('option');
});

it('limits the cart to the stock and does not go to the payment with prevent_oversell', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small] = shirtWithSizes(small: ['stock' => 5]);
    CartController::add($shirt->id, 5, $small->id);
    readyForCheckout();

    // Someone else bought three in the meantime
    $small->update(['stock' => 2]);

    Livewire::test(Checkout::class)
        ->call('gotoPayment')
        ->assertHasErrors('quantity.' . $shirt->id . '-' . $small->id)
        ->assertSee('Shirt (S): Only 2 left in stock.')
        ->assertNoRedirect();

    expect(cartLines()[0]->quantity)->toEqual(2)
        ->and(Order::whereNotNull('payment_id')->count())->toBe(0);
});

it('limits the quantity in the checkout to the stock with prevent_oversell', function () {
    config(['shopwire.prevent_oversell' => true]);
    [$shirt, $small] = shirtWithSizes(small: ['stock' => 2]);
    CartController::add($shirt->id, 1, $small->id);
    readyForCheckout();

    $key = $shirt->id . '-' . $small->id;

    Livewire::test(Checkout::class)
        ->set('quantity.' . $key, 9)
        ->assertHasErrors('quantity.' . $key)
        ->assertSee('Shirt (S): Only 2 left in stock.')
        ->assertSet('quantity.' . $key, 2);

    expect(cartLines()[0]->quantity)->toEqual(2);
});

it('ignores a product option when product options are not configured', function () {
    config(['shopwire.product_option_model' => null]);
    [$shirt, $small] = shirtWithSizes(small: ['price' => '99.00', 'stock' => 0]);

    // As before: the option id is stored with the cart item, but nothing is done with it
    expect(CartController::add($shirt->id, 2, $small->id))->toBeTrue();
    readyForCheckout();

    $line = cartLines()[0];

    expect($line->title)->toBe('Shirt')
        ->and((float) $line->price->price_including_vat)->toBe(24.2)
        ->and(Shopwire::productOptions($shirt))->toBeEmpty()
        ->and(array_column(Livewire::test(Checkout::class)->get('items'), 'id'))->toBe([$shirt->id, null]);
});
