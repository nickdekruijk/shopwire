<?php

use App\Models\Product;
use NickDeKruijk\Shopwire\Mail\OrderConfirmation;
use NickDeKruijk\Shopwire\Mail\OrderCreated;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\PaymentProviders\Mollie;

return [

    /*
    |--------------------------------------------------------------------------
    | migration
    |--------------------------------------------------------------------------
    | Include the migration if required
    */
    'migration' => true,

    /*
    |--------------------------------------------------------------------------
    | table_prefix
    |--------------------------------------------------------------------------
    | The tables created by the package will be prefixed with this string
    */
    'table_prefix' => 'shopwire_',

    /*
    |--------------------------------------------------------------------------
    | routes_prefix
    |--------------------------------------------------------------------------
    | Some actions like payment redirects and webhooks require unique urls. 
    | These routes will be created automaticaly and to avoid conflicts with 
    | other routes from the app you can add prefix to those urls here.
    | Webhook for payments will be /shopwire/payment/webhook for example.
    */
    'routes_prefix' => 'shopwire',

    /*
    |--------------------------------------------------------------------------
    | cache_prefix
    |--------------------------------------------------------------------------
    | Prefix to add to cache keys
    */
    'cache_prefix' => 'shopwire_',

    /*
    |--------------------------------------------------------------------------
    | cache_duration
    |--------------------------------------------------------------------------
    | Default duration in seconds to cache keys
    */
    'cache_duration' => 3600,

    /*
    |--------------------------------------------------------------------------
    | session_array
    |--------------------------------------------------------------------------
    | Shopwire will store some temporary data in this session array variable
    */
    'session_array' => 'shopwire_session',

    /*
    |--------------------------------------------------------------------------
    | product_model
    |--------------------------------------------------------------------------
    | The model to use for products
    */
    'product_model' => Product::class,

    /*
    |--------------------------------------------------------------------------
    | currency
    |--------------------------------------------------------------------------
    | The currency to use during checkout and Shopwire::money() helper
    */
    'currency' => [
        'code' => 'EUR',
        'symbol' => '€ ',
        'decimals' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | product_columns
    |--------------------------------------------------------------------------
    | Product columns used for checkout
    */
    'product_columns' => [
        'id' => 'id',
        'title' => 'title',
        'price' => 'price',
        'vat' => 'vat_id',    // Must match id from shopwire_vats table
        'stock' => 'stock',   // Will decrease after payment is successful 
        'weight' => 'weight', // Weight in grams, used to calculate shipping
    ],

    /*
    |--------------------------------------------------------------------------
    | checkout_url
    |--------------------------------------------------------------------------
    | The url to go to when customer clicks on checkout
    */
    'checkout_url' => Route::has('shopwire.checkout') ? route('shopwire.checkout') : '/checkout',

    /*
    |--------------------------------------------------------------------------
    | checkout_form
    |--------------------------------------------------------------------------
    | The customer details to request during checkout
    */
    'checkout_form' => [
        'company' => [
            'label_nl' => 'Bedrijfsnaam',
        ],
        'firstname' => [
            'validate' => 'required',
            'label_nl' => 'Voornaam',
        ],
        'lastname' => [
            'validate' => 'required',
            'label_nl' => 'Achternaam',
        ],
        'address' => [
            'validate' => 'required',
            'label_nl' => 'Adres',
        ],
        'postcode' => [
            'validate' => 'required',
        ],
        'city' => [
            'validate' => 'required',
            'label_nl' => 'Woonplaats',
        ],
        'country' => [
            'type' => 'country',
            'validate' => 'required',
            'label_nl' => 'Land',
        ],
        'phone' => [
            'validate' => 'nullable',
            'label_nl' => 'Telefoon',
        ],
        'remarks' => [
            'type' => 'textarea',
            'validate' => 'nullable',
            'label' => 'Any questions or remarks?',
            'label_nl' => 'Heb je vragen of opmerkingen?',
        ],
        'billing' => [
            'label' => 'Use different billing address?',
            'label_nl' => 'Gebruik ander adres voor factuur?',
            'type' => 'checkbox',
            'toggle_group' => 'billing',
        ],
        'billing_company' => [
            'validate' => 'required',
            'label' => 'Billing company',
            'label_nl' => 'Factuur bedrijfsnaam',
            'group' => 'billing',
        ],
        'billing_firstname' => [
            'validate' => 'required',
            'label' => 'Billing firstname',
            'label_nl' => 'Factuur voornaam',
            'group' => 'billing',
        ],
        'billing_lastname' => [
            'validate' => 'required',
            'label' => 'Billing lastname',
            'label_nl' => 'Factuur achternaam',
            'group' => 'billing',
        ],
        'billing_address' => [
            'validate' => 'required',
            'label' => 'Billing address',
            'label_nl' => 'Factuur adres',
            'group' => 'billing',
        ],
        'billing_postcode' => [
            'validate' => 'required',
            'label' => 'Billing postcode',
            'label_nl' => 'Factuur postcode',
            'group' => 'billing',
        ],
        'billing_city' => [
            'validate' => 'required',
            'label' => 'Billing city',
            'label_nl' => 'Factuur woonplaats',
            'group' => 'billing',
        ],
        'billing_country' => [
            'type' => 'country',
            'validate' => 'required',
            'label' => 'Billing country',
            'label_nl' => 'Factuur land',
            'group' => 'billing',
        ],
        'billing_email' => [
            'type' => 'email',
            'validate' => 'required|email',
            'label' => 'Billing email address',
            'label_nl' => 'Factuur e-mailadres',
            'group' => 'billing',
        ],
        'agree_with_terms' => [
            'label' => 'Yes, I agree with the general terms and conditions.',
            'label_nl' => 'Ja, ik ga akkoord met de algemene voorwaarden.',
            'type' => 'checkbox',
            'validate' => 'accepted',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | payment_provider
    |--------------------------------------------------------------------------
    | PaymentProvider to use, must have at least payment() and create() methods
    |
    */
    'payment_provider' => Mollie::class,

    /*
    |--------------------------------------------------------------------------
    | taxfree_countries
    |--------------------------------------------------------------------------
    | Some shops deliver goods to other countries without calculating VAT
    | This array contains the countries that are taxfree and customers for
    | these countries will see 0% VAT during checkout and payment.
    | Leave set to null if you want to use taxfree_countries_except instead
    |
    | Example for EU
    | 'taxfree_countries' => 'NL,AT,BE,BU,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,EI,IT,LV,LT,LU,MT,PL,PT,RO,SK,SI,ES,SE',
    */
    'taxfree_countries' => null,

    /*
    |--------------------------------------------------------------------------
    | taxfree_countries_except
    |--------------------------------------------------------------------------
    | Some shops deliver goods to other countries without calculating VAT
    | This array contains the countries that are excluded from taxfree and 
    | customers for these countries will see their normal VAT during checkout 
    | and payment but customers outside these countries will see 0% VAT.
    | Leave set to null if you want to use taxfree_countries instead
    |
    | Example for EU
    | 'taxfree_countries_except' => 'NL,AT,BE,BU,HR,CY,CZ,DK,EE,FI,FR,DE,GR,HU,EI,IT,LV,LT,LU,MT,PL,PT,RO,SK,SI,ES,SE',
    */
    'taxfree_countries_except' => null,

    /*
    |--------------------------------------------------------------------------
    | auth_guard
    |--------------------------------------------------------------------------
    | The authentication guard to use when loging in a customer
    */
    'auth_guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | owner_email
    |--------------------------------------------------------------------------
    | Email recipient for webshop owner notifications and new orders
    */
    'owner_email' => [
        'address' => env('SHOPWIRE_OWNER_ADDRESS', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'name' => env('SHOPWIRE_OWNER_NAME', env('MAIL_FROM_NAME', 'Example')),
    ],

    /*
    |--------------------------------------------------------------------------
    | email_from
    |--------------------------------------------------------------------------
    | Email sender to use for all notifications and order confirmations
    */
    'email_from' => [
        'address' => env('SHOPWIRE_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'name' => env('SHOPWIRE_FROM_NAME', env('MAIL_FROM_NAME', 'Example')),
    ],

    /*
    |--------------------------------------------------------------------------
    | mailable_customer
    |--------------------------------------------------------------------------
    | Send this mail to the customer when a new order is placed
    */
    'mailable_customer' => OrderConfirmation::class,

    /*
    |--------------------------------------------------------------------------
    | mailable_owner
    |--------------------------------------------------------------------------
    | Send this mail to the webshop owner when a new order is placed
    */
    'mailable_owner' => OrderCreated::class,

    /*
    |--------------------------------------------------------------------------
    | checkout_redirect_paid
    |--------------------------------------------------------------------------
    |
    | URL to redirect too after succesful payment
    |
    */
    'checkout_redirect_paid' => '/checkout/thankyou',

    /*
    |--------------------------------------------------------------------------
    | order_model
    |--------------------------------------------------------------------------
    |
    | If you want to customize the Order model then make a new model 
    | App\Models\Order class that extends \NickDeKruijk\Shopwire\Models\Order 
    | and change the value below to 'App\Models\Order',
    |
    */
    'order_model' => Order::class,
];
