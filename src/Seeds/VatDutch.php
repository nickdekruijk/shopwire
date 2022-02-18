<?php

namespace NickDeKruijk\Shopwire\Seeds;

use Illuminate\Database\Seeder;
use NickDeKruijk\Shopwire\Models\Vat;

class VatDutch extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = 1;
        Vat::updateOrCreate([
            'id' => $id++,
        ], [
            'high_rate' => true,
            'rate' => '21',
            'included' => true,
            'sort' => $id,
        ]);

        Vat::updateOrCreate([
            'id' => $id++,
        ], [
            'high_rate' => true,
            'rate' => '21',
            'included' => false,
            'sort' => $id,
        ]);

        Vat::updateOrCreate([
            'id' => $id++,
        ], [
            'high_rate' => false,
            'rate' => '9',
            'included' => true,
            'sort' => $id,
        ]);

        Vat::updateOrCreate([
            'id' => $id++,
        ], [
            'high_rate' => false,
            'rate' => '9',
            'included' => false,
            'sort' => $id,
        ]);

        Vat::updateOrCreate([
            'id' => $id++,
        ], [
            'description' => '0% (Vrijgesteld)',
            'high_rate' => false,
            'rate' => '0',
            'included' => false,
            'sort' => $id,
        ]);
    }
}
