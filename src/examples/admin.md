## Admin package modules example
Add and edit the modules below to your `config/admin.php` file.
```php
        'products' => [
            'view' => 'admin::model',
            'icon' => 'fa-gift',
            'title_nl' => 'Producten',
            'model' => 'App\Models\Product',
            'index' => 'name,price,home,vat.description',
            'active' => 'active',
            'orderBy' => 'sort',
            'sortable' => true,
            'columns' => [
                'active' => [
                    'title_nl' => 'Actief',
                ],
                'name' => [
                    'title_nl' => 'Product naam',
                    'validate' => 'required',
                ],
                'price' => [
                    'title_nl' => 'Prijs',
                    'type' => 'string',
                    'validate' => 'required|numeric|between:0.00,99999.99',
                ],
                'vat_id' => [
                    'title_nl' => 'BTW',
                    'type' => 'foreign',
                    'model' => 'NickDeKruijk\Shopwire\Models\Vat',
                    'columns' => 'description',
                    'orderby' => 'sort',
                    'validate' => 'required',
                ],
                'options' => [
                    'title' => 'Options / Variants',
                    'title_nl' => 'Opties / Varianten',
                    'type' => 'rows',
                    'model' => 'App\Models\ProductOption',
                    'self' => 'product_id',
                    'orderby' => 'sort',
                    'sortable' => true,
                    'active' => 'active',
                    'columns' => [
                        'title' => [
                            'title' => 'Short title',
                            'title_nl' => 'Korte titel',
                        ],
                        'description' => [
                            'title' => 'Descriptions (e.g. size / color)',
                            'title_nl' => 'Beschrijving (bijv. maat / kleur)',
                        ],
                        'price' => [
                            'title_nl' => 'Prijs',
                            'validate' => 'required|numeric|between:0.00,99999.99',
                        ],
                    ],
                ],
                'images' => [
                    'title_nl' => 'Afbeeldingen',
                    'type' => 'images',
                ],
                'description' => [
                    'title_nl' => 'Beschrijving',
                    'tinymce' => true,
                ],
            ],
        ],
        'orders' => [
            'view' => 'admin::model',
            'icon' => 'fa-shopping-cart',
            'title_nl' => 'Bestellingen',
            'model' => 'NickDeKruijk\Shopwire\Models\Order',
            'index' => 'id,customer.name,customer.email,amount,status,created_at,notes',
            'index_filters' => 'status',
            'active' => 'paid',
            'orderByDesc' => 'id',
            'columns' => [
                'paid' => [
                    'title_nl' => 'Betaald',
                ],
                'status' => [
                    'type' => 'select',
                    'values' => [
                        2 => 'Bevestigd',
                        4 => 'Wacht op inkoop producten',
                        6 => 'Klaar om te verzenden',
                        8 => 'Verzonden',
                        12 => 'Geannuleerd',
                        14 => 'Wacht op retour producten',
                        16 => 'Wacht op terugbetaling',
                        18 => 'Geretourneerd',
                    ],
                ],
                'notes' => [
                    'title_nl' => 'Notities',
                    'type' => 'text',
                ],
                'user_id' => [
                    'title_nl' => 'Gebruiker',
                    'type' => 'foreign',
                    'model' => 'App\Models\User',
                    'columns' => 'name,email',
                    'orderby' => 'name',
                ],
                'customer' => [],
                'lines' => [
                    'title' => 'Products',
                    'title_nl' => 'Producten',
                    'type' => 'rows',
                    'model' => 'NickDeKruijk\Shopwire\Models\OrderLine',
                    'self' => 'order_id',
                    'orderby' => 'sort',
                    'sortable' => true,
                    'width' => '100%',
                    // 'active' => 'active',
                    'columns' => [
                        'product_id' => [
                            'type' => 'foreign',
                            'model' => 'App\Models\Product',
                            'title' => 'Product',
                            'columns' => 'name',
                            'width' => '10%',
                            'autofill' => 'title,quantity,price,vat_rate,vat_included',
                        ],
                        'title' => [
                            'title_nl' => 'Titel',
                            'width' => '70%',
                        ],
                        'quantity' => [
                            'title' => 'Quantity',
                            'title_nl' => 'Aantal',
                            'validate' => 'required|numeric',
                            'width' => '5%',
                        ],
                        'price' => [
                            'title_nl' => 'Prijs',
                            'validate' => 'required|numeric|between:0.00,99999.99',
                            'width' => '8%',
                        ],
                        'vat_rate' => [
                            'title_nl' => 'BTW%',
                            'validate' => 'required|numeric|between:0.00,99.99',
                            'type' => 'string',
                            'width' => '5%',
                        ],
                        'vat_included' => [
                            'type' => 'boolean',
                            'title_nl' => 'Inc.',
                            'width' => '5%',
                        ],
                    ],
                ],
                // 'products' => [],
                // 'html' => [
                //     'type' => 'htmlview',
                // ],
            ],
        ],
        'discounts' => [
            'view' => 'admin::model',
            'icon' => 'fa-shopping-cart',
            'title_nl' => 'Kortingen en Coupons',
            'model' => 'NickDeKruijk\Shopwire\Models\Discount',
            'index' => 'title,date_start,date_end,coupon_code,discount_perc,discount_abs,free_shipping,amount_min',
            'active' => 'active',
            'orderBy' => 'sort',
            'sortable' => true,
            'columns' => [
                'active' => [
                    'title_nl' => 'Actief',
                ],
                'title' => [
                    'title_nl' => 'Titel',
                    'validate' => 'required',
                ],
                'description' => [
                    'title_nl' => 'Beschrijving',
                ],
                'date_start' => [
                    'title_nl' => 'Geldig vanaf',
                    'index_title_nl' => 'Van',
                    'validate' => 'nullable|date',
                ],
                'date_end' => [
                    'title_nl' => 'Geldig tot',
                    'index_title_nl' => 'Tot',
                    'validate' => 'nullable|date',
                ],
                'coupon_code' => [
                    'title_nl' => 'Coupon code (leeg indien voor iedereen geldig)',
                    'index_title_nl' => 'Coupon',
                ],
                // 'uses_per_user' => [
                //     'title_nl' => 'Aantal keer door ingelogde gebruikers te gebruiken (leeg voor onbeperkt)',
                //     'validate' => 'nullable|integer',
                // ],
                'discount_perc' => [
                    'title_nl' => 'Korting in percentage %',
                    'index_title_nl' => 'Korting %',
                    'validate' => 'nullable|numeric',
                ],
                'discount_abs' => [
                    'title_nl' => 'Korting in absoluut bedrag',
                    'index_title_nl' => 'Korting €',
                    'validate' => 'nullable|numeric',
                ],
                // 'apply_to_shipping' => [
                //     'title_nl' => 'Is de korting  ook van toepassing op verzendkosten?',
                // ],
                'free_shipping' => [
                    'title_nl' => 'Gratis verzending',
                ],
                'amount_min' => [
                    'title_nl' => 'Minimum bedrag in winkelwagen noodzakelijk',
                    'index_title_nl' => 'Vanaf',
                    'validate' => 'nullable|integer',
                ],
                'amount_min' => [
                    'title_nl' => 'Minimum bedrag in winkelwagen noodzakelijk',
                    'index_title_nl' => 'Vanaf',
                    'validate' => 'nullable|integer',
                ],
                'amount_max' => [
                    'title_nl' => 'Maximum bedrag in winkelwagen mogelijk',
                    'index_title_nl' => 'Tot',
                    'validate' => 'nullable|integer',
                ],
            ],
        ],
        'shipping' => [
            'view' => 'admin::model',
            'icon' => 'fa-truck',
            'title_nl' => 'Verzendkosten',
            'model' => 'NickDeKruijk\Shopwire\Models\ShippingRate',
            'index' => 'title,rate,amount_from,vat_included,countries,countries_except',
            'active' => 'active',
            'orderBy' => 'sort',
            'sortable' => true,
            'columns' => [
                'active' => [
                    'title_nl' => 'Actief',
                ],
                'title' => [
                    'title_nl' => 'Titel',
                    'validate' => 'required',
                ],
                'description' => [
                    'title_nl' => 'Omschrijving',
                    'type' => 'text',
                ],
                'rate' => [
                    'title_nl' => 'Prijs',
                    'validate' => 'required|numeric|between:0.00,99999.99',
                    'type' => 'string',
                ],
                'vat_included' => [
                    'title_nl' => 'BTW inclusief',
                ],
                'amount_from' => [
                    'title_nl' => 'Beschikbaar vanaf bestelbedrag',
                    'validate' => 'nullable|numeric|between:0.00,9999999.99',
                    'type' => 'string',
                ],
                'amount_to' => [
                    'title_nl' => 'Beschikbaar tot bestelbedrag',
                    'validate' => 'nullable|numeric|between:0.00,9999999.99',
                    'type' => 'string',
                ],
                'weight_from' => [
                    'title_nl' => 'Beschikbaar vanaf gewicht',
                    'validate' => 'nullable|numeric|between:0.000,9999999.999',
                    'type' => 'string',
                ],
                'weight_to' => [
                    'title_nl' => 'Beschikbaar tot gewicht',
                    'validate' => 'nullable|numeric|between:0.000,9999999.999',
                    'type' => 'string',
                ],
                'countries' => [
                    'title_nl' => 'Beschikbaar in alleen deze landen',
                    'placeholder_nl' => 'Bijvoorbeeld: NL, BE, L',
                ],
                'countries_except' => [
                    'title_nl' => 'Beschikbaar in alle landen behalve',
                    'placeholder_nl' => 'Bijvoorbeeld: NL, BE, L',
                ],
            ],
        ],
        'vat' => [
            'view' => 'admin::model',
            'icon' => 'fa-money',
            'title' => 'VAT Rates',
            'title_nl' => 'BTW Tarieven',
            'model' => 'NickDeKruijk\Shopwire\Models\Vat',
            'index' => 'description,rate,included,high_rate,shifted',
            'active' => 'active',
            'orderBy' => 'sort',
            'sortable' => true,
            'columns' => [
                'active' => [
                    'title_nl' => 'Actief',
                ],
                'description' => [
                    'title_nl' => 'Omschrijving',
                    'placeholder' => 'Excluding 21%',
                    'placeholder_nl' => 'Exclusief 21%',
                ],
                'rate' => [
                    'title_nl' => 'Percentage',
                    'validate' => 'required|numeric|between:0.00,99.99',
                    'type' => 'string',
                ],
                'included' => [
                    'title_nl' => 'Inclusief',
                ],
                'high_rate' => [
                    'title_nl' => 'Hoog tarief',
                ],
            ],
        ],
```