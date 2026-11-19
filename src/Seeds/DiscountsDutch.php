<?php

namespace NickDeKruijk\Shopwire\Seeds;

use Illuminate\Database\Seeder;
use NickDeKruijk\Shopwire\Models\Discount;

class DiscountsDutch extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a few example discounts, all inactive. Existing discounts are
     * left alone: an example is only created when there is no discount with
     * the same title yet.
     *
     * The minimum amount is compared with the products in the cart including
     * VAT, without shipping, and the amount has to be higher than the minimum.
     *
     * @return void
     */
    public function run()
    {
        Discount::firstOrCreate([
            'title' => 'Gratis verzending boven 100 euro',
        ], [
            'active' => false,
            'description' => 'Zodra iemand voor meer dan 100 euro aan producten in de winkelwagen heeft zitten is verzending gratis.',
            // 'date_start' => null,
            // 'date_end' => null,
            // 'discount_code' => null,
            'amount_min' => 100,
            // 'discount_perc' => null,
            // 'discount_abs' => null,
            // 'apply_to_shipping' => false,
            'free_shipping' => true,
        ]);
        Discount::firstOrCreate([
            'title' => 'Gratis verzending boven 50 euro',
        ], [
            'active' => false,
            'description' => 'Zodra iemand voor meer dan 50 euro aan producten in de winkelwagen heeft zitten is verzending gratis, maar nu met kortingscode.',
            // 'date_start' => null,
            // 'date_end' => null,
            'discount_code' => 'VERZENDKOSTEN0',
            'amount_min' => 50,
            // 'discount_perc' => null,
            // 'discount_abs' => null,
            // 'apply_to_shipping' => false,
            'free_shipping' => true,
        ]);
        Discount::firstOrCreate([
            'title' => '10% korting bij bestelling boven 25 euro',
        ], [
            'active' => false,
            'description' => 'Zodra iemand deze code invoert en voor meer dan 25 euro aan producten in de winkelwagen heeft zitten krijgt deze 10% korting op het totaalbedrag exclusief verzendkosten.',
            // 'date_start' => null,
            // 'date_end' => null,
            'discount_code' => 'KORTING10',
            'amount_min' => 25,
            'discount_perc' => 10,
            // 'discount_abs' => null,
            'apply_to_shipping' => false,
            // 'free_shipping' => false,
        ]);
        Discount::firstOrCreate([
            'title' => '15 euro korting bij bestelling boven 50 euro',
        ], [
            'active' => false,
            'description' => 'Zodra iemand deze code invoert en voor meer dan 50 euro aan producten in de winkelwagen heeft zitten krijgt deze 15 euro korting op het totaalbedrag exclusief verzendkosten.',
            // 'date_start' => null,
            // 'date_end' => null,
            'discount_code' => 'KORTING15',
            'amount_min' => 50,
            // 'discount_perc' => null,
            'discount_abs' => 15,
            'apply_to_shipping' => false,
            // 'free_shipping' => false,
        ]);
    }
}
