<?php

namespace NickDeKruijk\Shopwire\Seeds;

use Illuminate\Database\Seeder;
use NickDeKruijk\Shopwire\Models\ShippingRate;

class ShippingDutch extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $id = 1;

        ShippingRate::updateOrCreate([
            'id' => $id++,
        ], [
            'title' => app()->getLocale() == 'nl' ? 'Verzending binnen Nederland' : 'Shipping Netherlands',
            'rate' => '5',
            'countries' => 'NL',
            'countries_except' => null,
            'sort' => $id,
        ]);
        ShippingRate::updateOrCreate([
            'id' => $id++,
        ], [
            'title' => app()->getLocale() == 'nl' ? 'Verzending binnen EU' : 'Shipping EU',
            'rate' => '25',
            'countries' => 'AT,BE,BU,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,EI,IT,LV,LT,LU,MT,PL,PT,RO,SK,SI,ES,SE',
            'countries_except' => null,
            'sort' => $id,
        ]);
        ShippingRate::updateOrCreate([
            'id' => $id++,
        ], [
            'title' => app()->getLocale() == 'nl' ? 'Wereldwijde standaard verzending (7-30 dagen)' : 'Shipping worldwide standard (7-30 days)',
            'rate' => '30',
            'countries' => null,
            'countries_except' => 'NL,AT,BE,BU,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,EI,IT,LV,LT,LU,MT,PL,PT,RO,SK,SI,ES,SE',
            'sort' => $id,
        ]);
        ShippingRate::updateOrCreate([
            'id' => $id++,
        ], [
            'title' => app()->getLocale() == 'nl' ? 'Wereldwijde express verzending (3-10 dagen)' : 'Shipping worldwide express (3-10 days)',
            'rate' => '65',
            'countries' => null,
            'countries_except' => 'NL,AT,BE,BU,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,EI,IT,LV,LT,LU,MT,PL,PT,RO,SK,SI,ES,SE',
            'sort' => $id,
        ]);
        ShippingRate::updateOrCreate([
            'id' => $id++,
        ], [
            'active' => false,
            'title' => app()->getLocale() == 'nl' ? 'Gratis verzending' : 'Free shipping',
            'rate' => '0',
            'amount_from' => '1000',
            'countries' => null,
            'countries_except' => null,
            'sort' => $id,
        ]);
    }
}
