<?php

namespace NickDeKruijk\Shopwire\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Models\Discount;

class DiscountCodeValid implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        // Compare with the same amount the cart uses to apply a discount: the products without shipping.
        // With the total amount a code could be accepted while the cart doesn't apply it.
        if ($value && !Discount::valid($value, CartController::getItems()->statistics['amount_only_items'])->count()) {
            $fail(__('shopwire::cart.discount_code_invalid'));
        }
    }
}
