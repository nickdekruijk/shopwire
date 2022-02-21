<?php

namespace NickDeKruijk\Shopwire\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;

class CartController extends Controller
{
    /**
     * Get current Cart based on sessionId or user.
     *
     * @param  boolean $create If true create and store a new Cart instance if no existing cart is found
     * @return Cart
     */
    public static function currentCart($create = false)
    {
        // Session variable to store cart id in
        $session_cart_id = config('webshop.table_prefix') . 'cart_id';

        // First check if there is a valid cart_id stored in the session
        if (session($session_cart_id)) {
            $cart = Cart::find(session($session_cart_id));
            if ($cart) {
                // Found it, return it
                return $cart;
            }
        }

        // Secondly check if there the current session_id has a cart saved
        $cart = Cart::where('session_id', session()->getId())->latest()->first();
        if ($cart) {
            // Found it, store it in session and return it
            session([$session_cart_id => $cart->id]);
            return $cart;
        }

        // Thirdly check if the current user has a cart
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::user()->id)->latest()->first();
            if ($cart) {
                // Found it, store it in session and return it
                session([$session_cart_id => $cart->id]);
                return $cart;
            }
        }

        // Still no Cart? Create a new one if requested
        if ($create) {
            $cart = new Cart;
            $cart->session_id = session()->getId();
            if (Auth::check()) {
                $cart->user_id = Auth::user()->id;
            }
            $cart->save();
            // Store the id in the session for performance
            session([$session_cart_id => $cart->id]);
            // And return it
            return $cart;
        }
    }

    /**
     * Add a product to the cart.
     *
     * @param  integer $product_id
     * @param  integer $quantity
     * @param  integer $product_option_id
     * @return Response
     */
    public static function add($product, $quantity = 1, $product_option_id = null)
    {
        // Get the current cart, create if needed
        $cart = self::currentCart(true);

        // Check if product is already in cart
        $cart_item = $cart->items()->where('product_id', $product->id)->where('product_option_id', $product_option_id)->first();
        if ($cart_item) {
            // Already in cart, increase quantity
            $cart_item->quantity = $cart_item->quantity + $quantity;
        } else {
            // Create a new one instead
            $cart_item = new CartItem;
            $cart_item->cart_id = $cart->id;
            $cart_item->product_id = $product->id;
            $cart_item->product_option_id = $product_option_id;
            $cart_item->quantity = $quantity;
        }

        // Save it
        $cart_item->save();

        return true;
    }

    /**
     * Return all cart items and calculate total amounts, discount and VAT.
     *
     * @param  integer $coupon_code Apply discount for a coupon code.
     * @return object
     */
    public static function getItems($coupon_code = null)
    {
        // Get cart contents
        $cart = self::currentCart();
        if (!$cart) {
            return [];
        }

        // Initialize response object
        $response = (object) [
            'items' => [],
            'amount_including_vat' => 0,
            'amount_excluding_vat' => 0,
            'amount_only_items' => 0,
            'amount_vat' => [],
            'weight' => 0,
            'count' => 0,
            'count_unique' => 0,
        ];

        // Used for tracking the highest VAT rate to calculate shipping costs
        $max_vat_rate = 0;

        // Check if product_option is used
        $with = ['product'];

        // Walk thru all items in the cart and calculate VAT
        foreach ($cart->items()->with($with)->where('quantity', '!=', 0)->get() as $item) {
            $response->items[] = (object) [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'title' => $item->title,
                'price' => $item->price,
                'weight' => $item->weight,
                'quantity' => +$item->quantity,
            ];

            $response->amount_including_vat += $item->price->price_including_vat * $item->quantity;
            $response->amount_excluding_vat += $item->price->price_excluding_vat * $item->quantity;
            $response->amount_vat[$item->price->vat_rate] = ($response->amount_vat[$item->price->vat_rate] ?? 0) + ($item->price->price_including_vat - $item->price->price_excluding_vat) * $item->quantity;

            if ($item->price->vat_rate > $max_vat_rate) {
                $max_vat_rate = $item->price->vat_rate;
            }
            $response->weight += $item->weight * $item->quantity;;

            $response->count += $item->quantity;
            $response->count_unique++;
        }

        return $response;
    }
}
