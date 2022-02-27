<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Countries;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Controllers\PaymentController;
use NickDeKruijk\Shopwire\Shopwire;

class Checkout extends Component
{
    public $account;
    public $statistics;
    public $items;
    public $quantity = [];
    public $includingVat = true;
    public $show_zero = false;
    public $shipping;
    public $shipping_options;
    public $countries;
    public $form;
    public $form_columns = [];
    public $form_groups = [];
    public $payment_methods;
    public $payment_method;
    public $payment_issuer;
    public $password;
    public $password_confirmation;

    protected $listeners = [
        'cartUpdate' => 'cartUpdate',
    ];

    public function mount()
    {
        // Get the countries and codes from the countries package
        $this->countries = Countries::getList(app()->getLocale());

        // Get stored form values from session
        $this->form = Shopwire::session('checkout_form');

        // If user is loggedin set account to 'login' else get account method from session
        if (Shopwire::auth()->check()) {
            $this->account = 'login';
        } else {
            $this->account = Shopwire::session('checkout_account') ?: 'login';
        }

        // Track group toggle status
        $group_status = [];

        // Set form_columns and form_groups
        foreach (config('shopwire.checkout_form') as $column => $attributes) {

            // $attributes should be an array
            if (!is_array($attributes)) {
                $column = $attributes;
                $attributes = [];
            }

            // Set the label based on either the localized label, default label or the column name
            $attributes['label'] =
                $attributes['label_' . app()->getLocale()]
                ?? $attributes['label']
                ?? ucfirst(str_replace('_', ' ', $column));

            // Set the column type or use text as default
            $attributes['type'] = $attributes['type'] ?? 'text';

            if (isset($attributes['toggle_group'])) {
                $group_status[$attributes['toggle_group']] = $this->form[$column] ?? false;
            }

            // Check for form groups
            if (isset($attributes['group'])) {
                $this->form_groups['form.' . $attributes['group']] = $group_status[$attributes['group']] ?? false;
            } else {
                $attributes['group'] = null;
            }

            // Add the column to the form
            $this->form_columns[$column] = $attributes;
        }

        // Get the items from the cart
        $this->cartUpdate();

        // Get the payment methods
        $this->payment_methods = PaymentController::methods();
        // Get the payment method and issuer from session
        $this->payment_method = Shopwire::session('checkout_payment_method');
        $this->payment_issuer = Shopwire::session('checkout_payment_issuer');

        // If only one payment mehtod is available, set it as default
        if (count($this->payment_methods) == 1) {
            $this->payment_method = key($this->payment_methods);
        }
    }

    public function cartUpdate()
    {
        $cart = CartController::getItems();
        $this->form['country'] = $cart->cart->country_code;
        $this->shipping = $cart->cart->shipping_rate_id;
        $this->items = [];
        foreach ($cart->items as $item) {
            if ($item == 'select_shipping') {
                $this->items[] = [
                    'id' => null,
                    'title' => $item,
                    'price' => 0,
                ];
            } elseif ($this->show_zero || $item->quantity != 0) {
                $this->quantity[$item->product_id] = $item->quantity;
                $this->items[] = [
                    'id' => $item->product_id,
                    'title' => $item->title,
                    'url' => $item->product_id ? $item->product->url : null,
                    'price' => $this->includingVat ? $item->price->price_including_vat : $item->price->price_excluding_vat,
                ];
            }
        }
        $this->show_zero = true;
        $this->statistics = $cart->statistics;
        $this->shipping_options = $cart->shipping_options;
        if (count($this->shipping_options) == 1) {
            $this->shipping = key($this->shipping_options);
        }
    }

    public function updated($attribute, $value)
    {
        if ($attribute == 'including_vat') {
            $this->cartUpdate();
            return;
        }
        if ($attribute == 'shipping') {
            CartController::set('shipping_rate_id', $this->shipping);
            $this->cartUpdate();
        }
        if ($attribute == 'form.country') {
            // Also store the country code in the cart to calculate the shipping
            CartController::set('country_code', $value);
            $this->cartUpdate();

            // Also set billing country if it's empty or when billing country is hidden
            if (empty($this->form['billing_country']) || (!$this->form_groups['form.billing'] && $this->form['billing_country'] == Shopwire::session('checkout_form.country'))) {
                $this->form['billing_country'] = $value;
                Shopwire::session(['checkout_form.billing_country' => $value]);
            }
        }

        if ($attribute != 'password') {
            Shopwire::session(['checkout_' . $attribute => $value]);
        }

        if (isset($this->form_columns[substr($attribute, 5)]['toggle_group'])) {
            $this->form_groups['form.' . $this->form_columns[substr($attribute, 5)]['toggle_group']] = $value;
            if (!$value) {
                $this->validateOnly($attribute);
            }
        } else {
            $this->validateOnly($attribute);
        }
    }

    public function updatedQuantity($quantity, $product_id)
    {
        if ($quantity < 0) {
            $quantity = 0;
        }
        CartController::update($product_id, $quantity);
        $this->emit('cartUpdate');
    }

    public function render()
    {
        return view('shopwire::checkout');
    }

    public function login()
    {
        if (!Shopwire::auth()->attempt(['email' => $this->form['email'], 'password' => $this->form['password']])) {
            $this->addError('form.email', __('shopwire::cart.login_invalid'));
            $this->addError('form.password', __('shopwire::cart.login_invalid'));
        }
    }

    public function logout()
    {
        Shopwire::auth()->logout();
    }

    protected $validationAttributes = [];

    protected $messages = [];

    protected function rules()
    {
        $rules = [
            'form.email' => 'required|email',
            'shipping' => 'required',
            'payment_method' => 'required',
            'payment_issuer' => 'required_if:payment_method,ideal',
        ];

        $this->messages['payment_issuer.required_if'] = __('shopwire::cart.payment_issuer_required');

        $this->validationAttributes = [
            'form.email' => __('shopwire::cart.email'),
            'form.password' => __('shopwire::cart.password'),
            'form.payment_method' => __('shopwire::cart.payment_method'),
        ];

        foreach ($this->form_columns as $column => $attributes) {
            if (isset($attributes['validate']) && (!isset($attributes['group']) || $this->form_groups['form.' . $attributes['group']])) {
                $rules['form.' . $column] = $attributes['validate'];
                $this->validationAttributes['form.' . $column] = $attributes['label'];
            }
        }
        if ($this->account == 'login') {
            if (Shopwire::auth()->check()) {
                $this->form['email'] = Shopwire::auth()->user()->email;
            } else {
                $rules['form.password'] = 'required';
            }
        }
        if ($this->account == 'create') {
            $rules['form.password'] = 'required|confirmed';
            $rules['form.password_confirmation'] = 'required';
        }
        return $rules;
    }

    public function gotoPayment()
    {
        $this->validate();
        $this->login();
    }
}
