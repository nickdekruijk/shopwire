<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Countries;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Controllers\PaymentController;
use NickDeKruijk\Shopwire\Models\OrderLine;
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

        // Get and cache the payment methods
        $this->payment_methods = cache()->remember(config('shopwire.cache_prefix') . 'payment_methods', config('shopwire.cache_duration'), fn () => PaymentController::methods());

        // Get the payment method and issuer from session
        $this->payment_method = Shopwire::session('checkout_payment_method');
        $this->payment_issuer = Shopwire::session('checkout_payment_issuer');

        // If only one payment mehtod is available, set it as default
        if (count($this->payment_methods) == 1) {
            $this->payment_method = key($this->payment_methods);
        }

        // Store the current url in session for redirecting after failed payment verification
        Shopwire::session(['checkout_url' => url()->current()]);
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

        // If attribute isn't a password store the value in session
        if (!str_contains($attribute, 'password')) {
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
            return false;
        } else {
            return true;
        }
    }

    public function logout()
    {
        Shopwire::auth()->logout();
    }

    protected function validationAttributes()
    {
        $validationAttributes = [
            'form.email' => __('shopwire::cart.email'),
            'form.password' => __('shopwire::cart.password'),
            'form.password_confirmation' => __('shopwire::cart.password_confirmation'),
            'form.payment_method' => __('shopwire::cart.payment_method'),
        ];

        foreach ($this->form_columns as $column => $attributes) {
            $validationAttributes['form.' . $column] = $attributes['label'];
        }

        return $validationAttributes;
    }

    protected function messages()
    {
        return [
            'payment_issuer.required_if' => __('shopwire::cart.payment_issuer_required'),
        ];
    }

    /**
     * Set the validation rules for the checkout
     *
     * @return array
     */
    protected function rules(): array
    {
        $rules = [
            'form.email' => 'required|email',
            'shipping' => 'required',
            'payment_method' => 'required',
            'payment_issuer' => 'required_if:payment_method,ideal',
        ];

        foreach ($this->form_columns as $column => $attributes) {
            if (isset($attributes['validate']) && (!isset($attributes['group']) || $this->form_groups['form.' . $attributes['group']])) {
                $rules['form.' . $column] = $attributes['validate'];
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
            $rules['form.email'] = 'required|email:rfc,strict,dns,spoof,filter|unique:users,email';
            $rules['form.password'] = ['required', Password::min(8)->uncompromised()->letters()->numbers()->mixedCase()->symbols(), 'confirmed'];
            $rules['form.password_confirmation'] = 'required';
        }

        return $rules;
    }

    public function gotoPayment()
    {
        // Clear payment errors if any
        Shopwire::session(['payment_error' => null]);

        // Validate all input
        $this->validate();

        // Attempt to login if not loggedin yet
        if ($this->account == 'login' && Shopwire::auth()->guest()) {
            if (!$this->login()) {
                return false;
            }
        }

        // Create account if customer wants to
        if ($this->account == 'create') {
            $model = Shopwire::auth()->getProvider()->getModel();
            $user = (new $model)->create([
                'email' => $this->form['email'],
                'name' => $this->form['firstname'] . ' ' . $this->form['lastname'],
                'password' => bcrypt($this->form['password']),
            ]);
            // Login with the new account
            if (!Shopwire::auth()->attempt(['email' => $user->email, 'password' => $this->form['password']])) {
                abort(500, 'Failed to login new user');
            }
        }

        // Get the Order object and update it
        $order = Shopwire::order();
        $order->user_id = Shopwire::auth()->user()->id ?? null;

        // Set the customer details
        $order->customer = $this->form;
        $order->customer = $order->customerSorted;

        // Save the products in the order
        $cart = CartController::getItems();
        $order->html = view('shopwire::cart-html', ['cart' => $cart])->render();
        foreach ($cart->items as $item) {
            // We don't want the Product model in this array
            unset($item->product);
            $products[] = $item;
        }
        $order->products = $products;
        $order->amount = $cart->statistics['amount_including_vat'];
        $order->save();

        // Attach OrderLine rows
        $order->lines()->delete();
        foreach ($cart->items as $item) {
            $orderline = new Orderline;
            $orderline->order_id = $order->id;
            $orderline->product_id = $item->product_id ?? null;
            $orderline->product_option_id = $item->product_option_id ?? null;
            $orderline->title = $item->title;
            $orderline->quantity = $item->quantity;
            $orderline->weight = $item->weight ?? null;
            $orderline->price = $item->price->price_including_vat;
            $orderline->vat_rate = $item->price->vat_rate;
            $orderline->vat_included = $item->price->vat_included;
            $orderline->save();
        }

        // Get payment id and set redirect/webhook urls
        $payment = PaymentController::create([
            'amount' => $order->amount,
            'currency' => 'EUR',
            'description' => __('shopwire::cart.webshop_order') . $order->id,
            'webhookUrl' => app()->environment() == 'local' ? null : route('shopwire-payment-webhook'),
            'redirectUrl' => route('shopwire-payment-verify'),
            'method' => $this->payment_method,
            'issuer' => $this->payment_issuer,
        ]);
        $order->payment_id = $payment->id;
        $order->payment_method = $this->payment_method;
        $order->save();

        // Redirect to payment provider
        Shopwire::log('info', 'Payment redirect: ' . $order->id . ' ' . $order->payment_id . ' ' . $order->customer['email'] . ' ' . $payment->webhookUrl);
        return redirect($payment->checkoutUrl, 303);
    }
}
