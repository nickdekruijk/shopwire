<?php

namespace NickDeKruijk\Shopwire\Controllers;

use App\Http\Controllers\Controller;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;
use NickDeKruijk\Shopwire\Models\Discount;
use NickDeKruijk\Shopwire\Models\ShippingRate;
use NickDeKruijk\Shopwire\Shopwire;
use Stevebauman\Location\Facades\Location;

class CartController extends Controller
{
    /**
     * Get current Cart based on sessionId or user.
     *
     * @param  boolean $create If true create and store a new Cart instance if no existing cart is found
     * @return Cart
     */
    public static function getCart($create = false)
    {
        // First check if there is a valid cart_id stored in the session
        if (Shopwire::session('cart_id')) {
            $cart = Cart::find(Shopwire::session('cart_id'));
            if ($cart) {
                // Found it, return it
                return $cart;
            }
        }

        // Secondly, check if there the current session_id has a cart saved
        $cart = Cart::where('session_id', session()->getId())->latest()->first();
        if ($cart) {
            // Found it, store it in session and return it
            Shopwire::session(['cart_id' => $cart->id]);
            return $cart;
        }

        // Thirdly, check if the current user has a cart
        if (Shopwire::auth()->check()) {
            $cart = Cart::where('user_id', Shopwire::auth()->user()->id)->latest()->first();
            if ($cart) {
                // Found it, store it in session and return it
                Shopwire::session(['cart_id' => $cart->id]);
                return $cart;
            }
        }

        // Still no Cart? Create a new one if requested
        if ($create) {
            $cart = new Cart;
            $cart->session_id = session()->getId();
            if (Shopwire::auth()->check()) {
                $cart->user_id = Shopwire::auth()->user()->id;
            }
            $cart->country_code = Location::get()->countryCode ?? null;

            $cart->save();
            // Store the id in the session for performance
            Shopwire::session(['cart_id' => $cart->id]);
            // And return it
            return $cart;
        }
    }

    /**
     * Return total count of items in the cart.
     *
     * @param  boolean $unique When true return total amount of unique items instead of adding all quantities together.
     * @return integer
     */
    public static function count($unique = false)
    {
        $cart = self::getCart();
        $count = 0;
        if ($cart) {
            foreach ($cart->items as $item) {
                $count += $unique ? ($item->quantity == 0 ? 0 : 1) : $item->quantity;
            }
        }
        return $count;
    }

    /**
     * Set a cart column value
     *
     * @param string $column
     * @param mixed $value
     * @return boolean
     */
    public static function set(string $column, mixed $value): bool
    {
        $cart = self::getCart();
        if ($cart) {
            $cart->$column = $value ?: null;
            return $cart->save();
        } else {
            return false;
        }
    }

    /**
     * Update or add a product to the cart with a fixed quantity
     *
     * @param  integer $product_id
     * @param  float $quantity
     * @param  integer $product_option_id
     * @return Response
     */
    public static function update($product_id, $quantity = 1, $product_option_id = null)
    {
        return self::add($product_id, $quantity, $product_option_id, false);
    }

    /**
     * Add a product to the cart.
     *
     * @param  integer $product_id
     * @param  float $quantity
     * @param  integer $product_option_id
     * @param  bool $incremental When true, increment the quantity of the item if it already exists in the cart, else set fixed quantity
     * @return Response
     */
    public static function add($product_id, $quantity = 1, $product_option_id = null, $incremental = true)
    {
        // Get the current cart, create if needed
        $cart = self::getCart(true);

        // Check if product is already in cart
        $cart_item = $cart->items()->where('product_id', $product_id)->where('product_option_id', $product_option_id)->first();
        if ($cart_item) {
            // Already in cart, increase quantity
            $cart_item->quantity = $incremental ? $cart_item->quantity + $quantity : $quantity;
        } else {
            // Create a new one instead
            $cart_item = new CartItem;
            $cart_item->cart_id = $cart->id;
            $cart_item->product_id = $product_id;
            $cart_item->product_option_id = $product_option_id;
            $cart_item->quantity = $quantity;
        }

        // Save it
        $cart_item->save();

        return true;
    }

    public static function taxfree($country_code)
    {
        return (config('shopwire.taxfree_countries_except') && !in_array($country_code, explode(',', config('shopwire.taxfree_countries_except'))))
            || (config('shopwire.taxfree_countries')        &&  in_array($country_code, explode(',', config('shopwire.taxfree_countries'))));
    }

    /**
     * Return all cart items and calculate total amounts, discount and VAT.
     *
     * @param  integer $discount_code Apply discount for a discount code.
     * @return object
     */
    public static function getItems($discount_code = null)
    {
        // Get cart contents
        $cart = self::getCart();
        if (!$cart) {
            return [];
        }

        // Initialize response object
        $response = (object) [
            'items' => [],
            'statistics' => [
                'amount_including_vat' => 0,
                'amount_excluding_vat' => 0,
                'amount_only_items' => 0,
                'amount_vat' => [],
                'weight' => 0,
                'count' => 0,
                'count_unique' => 0,
            ],
            'cart' => $cart,
        ];

        // Used for tracking the highest VAT rate to calculate shipping costs
        $max_vat_rate = 0;

        // Check if product_option is used
        $with = ['product'];

        // Walk thru all items in the cart and calculate VAT
        foreach ($cart->items()->with($with)->get() as $item) {
            // Get price details and vat rates
            $price = self::taxfree($cart->country_code) ? $item->product->shopwire_price_taxfree : $item->product->shopwire_price;

            $response->items[] = (object) [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'title' => $item->product->title,
                'price' => $price,
                'weight' => $item->product->weight,
                'product' => $item->product,
                'quantity' => +$item->quantity,
            ];

            $response->statistics['amount_including_vat'] += $price->price_including_vat * $item->quantity;
            $response->statistics['amount_excluding_vat'] += $price->price_excluding_vat * $item->quantity;
            $response->statistics['amount_vat'][$price->vat_rate] = ($response->amount_vat[$price->vat_rate] ?? 0) + ($price->price_including_vat - $price->price_excluding_vat) * $item->quantity;

            if ($item->quantity > 0 && $price->vat_rate > $max_vat_rate) {
                $max_vat_rate = $price->vat_rate;
            }
            $response->statistics['weight'] += $item->product->weight * $item->quantity;;

            $response->statistics['count'] += $item->quantity;
            $response->statistics['count_unique']++;
        }

        // Amount only items is used for discounts that should not apply to shipping costs
        $response->statistics['amount_only_items'] = $response->statistics['amount_including_vat'];

        // Fetch all available shipping rates
        $shipping_rates = ShippingRate::valid($response->statistics['amount_including_vat'], $response->statistics['weight'], $cart->country_code)->get();

        // Find selected shipping rate and generate the options if available
        $shipping_rate = null;
        $shipping_options = [];
        if ($shipping_rates->count() == 1) {
            $shipping_rate = $shipping_rates->first();
            $shipping_options[$shipping_rate->id] = $shipping_rate->toArray();
        } elseif ($shipping_rates->count() > 1) {
            foreach ($shipping_rates as $rate) {
                $shipping_options[$rate->id] = $rate->toArray();
                if ($cart->shipping_rate_id == $rate->id) {
                    $shipping_rate = $rate;
                }
            }
        }
        $response->shipping_options = $shipping_options;

        // Fetch all available discounts
        $discounts = Discount::active($response->statistics['amount_including_vat'])->get();

        // Check if customer is eligible for free shipping
        foreach ($discounts as $discount) {
            if ($discount_code == $discount->discount_code || !$discount->discount_code) {
                if ($discount->free_shipping && $shipping_rate) {
                    $shipping_rate->rate = 0;
                }
            }
        }

        // Create shipping item and calculate VAT
        if ($shipping_rate) {
            $shipping = (object) [
                'id' => null,
                'product_id' => null,
                'type' => 'shipping',
                'title' => $shipping_rate->title,
                'price' => (object) [
                    'price' => $shipping_rate->rate,
                    'vat_included' => $shipping_rate->vat_included,
                    'vat_rate' => $max_vat_rate,
                    'price_including_vat' => null,
                    'price_excluding_vat' => null,
                    'price_vat' => null,
                ],
                'quantity' => 1,
            ];

            if ($shipping->price->vat_included) {
                $shipping->price->price_including_vat = $shipping_rate->rate;
                $shipping->price->price_excluding_vat = round($shipping_rate->rate / ($max_vat_rate / 100 + 1), 2);
            } else {
                $shipping->price->price_including_vat = round($shipping_rate->rate * ($max_vat_rate / 100 + 1), 2);
                $shipping->price->price_excluding_vat = $shipping_rate->rate;
            }
            $response->statistics['amount_vat'][$max_vat_rate] = ($response->statistics['amount_vat'][$max_vat_rate] ?? 0) + $shipping->price->price_including_vat - $shipping->price->price_excluding_vat;

            $response->statistics['amount_including_vat'] += $shipping->price->price_including_vat;
            $response->statistics['amount_excluding_vat'] += $shipping->price->price_excluding_vat;
            $response->items[] = $shipping;
        } else {
            $response->items[] = 'select_shipping';
        }

        $response->statistics['has_discount_applied'] = false;

        foreach ($discounts as $discount) {
            if ($discount_code == $discount->discount_code || !$discount->discount_code) {
                $discountAmount = round(-$discount->discount_abs - ($discount['apply_to_shipping'] ? $response->statistics['amount_including_vat'] : $response->statistics['amount_only_items']) * $discount->discount_perc / 100, 2);
                $response->statistics['amount_including_vat'] += $discountAmount;
                $response->statistics['has_discount_applied'] = true;
                $response->items[] = (object) [
                    'id' => null,
                    'product_id' => null,
                    'type' => 'discount',
                    'title' => $discount->title . ($discount->discount_code ? ' (' . $discount->discount_code . ')' : ''),
                    'price' => (object) [
                        'price' => $discountAmount,
                        'vat_included' => true,
                        'vat_rate' => null,
                        'price_including_vat' => $discountAmount,
                        'price_excluding_vat' => $discountAmount,
                        'price_vat' => $discountAmount,
                    ],
                    'quantity' => 1,
                ];
            }
        }

        $response->items = collect($response->items);
        return $response;
    }

    /**
     * Empty the current users shopping cart
     *
     * @return void
     */
    public static function empty()
    {
        Shopwire::log('debug', 'Empty cart: ' . Shopwire::session('checkout_form.email'));
        $cart = self::getCart();
        if ($cart) {
            $cart->delete();
        }
    }
}
