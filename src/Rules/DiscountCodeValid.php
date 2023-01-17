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
        if ($value && !Discount::valid($value, CartController::getItems()->statistics['amount_including_vat'])->count()) {
            $fail(__('shopwire::cart.discount_code_invalid'));
        }
    }
}
