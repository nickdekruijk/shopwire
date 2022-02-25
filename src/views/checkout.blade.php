<div class="shopwire-checkout">
    <div class="shopwire-checkout-cart">
        <h3>@lang('shopwire::cart.cart')</h3>
        <table>
            <tr>
                <th class="shopwire-checkout-product">@lang('shopwire::cart.product')</th>
                <th class="shopwire-checkout-price">@lang('shopwire::cart.price')</th>
                <th class="shopwire-checkout-quantity">@lang('shopwire::cart.quantity')</th>
                <th class="shopwire-checkout-total">@lang('shopwire::cart.total')</th>
            </tr>
            @foreach($items as $product)
                @if ($product['id'])
                    <tr>
                        <td class="shopwire-checkout-product">
                            @if ($product['url'])
                                <a href="{{ $product['url'] }}">{{ $product['title'] }}</a>
                            @else
                                {{ $product['title'] }}
                            @endif
                        </td>
                        <td class="shopwire-checkout-price">{{ Shopwire::money($product['price']) }}</td>
                        <td class="shopwire-checkout-quantity"><input type="number" wire:model="quantity.{{ $product['id'] }}"></td>
                        <td class="shopwire-checkout-total">{{ Shopwire::money($quantity[$product['id']] * $product['price']) }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="3" class="shopwire-checkout-shipping">
                            @if (count($shipping_options) > 1)
                                <span class="shopwire-checkout-select"><select wire:model="shipping">
                                    <option value="">@lang('shopwire::cart.select-shipping')</option>
                                    @foreach($shipping_options as $shipping_rate)
                                        <option value="{{ $shipping_rate['id'] }}">{{ $shipping_rate['title'] }}</option>
                                    @endforeach
                                </select></span>
                            @elseif ($product['title'] == 'select_shipping')
                                @lang('shopwire::cart.no-shipping-possible')
                            @else
                                {{ $product['title'] }}
                            @endif
                        </td>
                        <td class="shopwire-checkout-shipping shopwire-checkout-total">{{ $product['price'] ? Shopwire::money($product['price']) : '' }}</td>
                    </tr>
                @endif
            @endforeach
            @if ($includingVat)
                <tr>
                    <td colspan="3" class="shopwire-checkout-subtotal">@lang('shopwire::cart.total_to_pay')</td>
                    <td class="shopwire-checkout-subtotal">{{ Shopwire::money($statistics['amount_including_vat']) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="3" class="shopwire-checkout-subtotal">@lang('shopwire::cart.subtotal_vatExcl')</td>
                    <td class="shopwire-checkout-subtotal">{{ Shopwire::money($statistics['amount_excluding_vat']) }}</td>
                </tr>
                @foreach($statistics['amount_vat'] as $rate => $amount)
                    @if ($amount)
                        <tr>
                            <td></td>
                            <td class="shopwire-checkout-vat">@lang('shopwire::cart.vat')</td>
                            <td class="shopwire-checkout-vat">{{ $rate+0 }} %</td>
                            <td class="shopwire-checkout-total">{{ Shopwire::money($amount) }}</td>
                        </tr>
                    @endif
                @endforeach
                <tr>
                    <td></td>
                    <td colspan="2" class="shopwire-checkout-subtotal">@lang('shopwire::cart.subtotal_vatIncl')</td>
                    <td class="shopwire-checkout-subtotal">{{ Shopwire::money($statistics['amount_including_vat']) }}</td>
                </tr>
            @endif
            <tr class="shopwire-checkout-vat-toggle">
                <td></td>
                <td colspan="3"><label><input type="checkbox" wire:model="includingVat">@lang('shopwire::cart.vat_toggle')<span></span></label></td>
            </tr>
        </table>
        <div class="shopwire-checkout-payment-account">
            <div class="shopwire-checkout-payment">
                <h3>@lang('shopwire::cart.payment')</h3>
                @foreach($payment_methods as $method)
                    <label class="shopwire-checkout-payment-method">
                        @if (count($payment_methods) > 1)
                            <input type="radio" wire:model="payment_method" value="{{ $method['id'] }}"><span></span>
                        @endif
                        {{ $method['description'] }}
                        @if (isset($method['issuers']) && $payment_method == $method['id'])
                            <span class="shopwire-checkout-select shopwire-checkout-payment-issuer"><select wire:model="payment_issuer" class="">
                                <option value="">@lang('shopwire::cart.payment_select_issuer')</option>
                                @foreach($method['issuers'] as $issuer_id => $issuer)
                                    <option value="{{ $issuer_id }}">{{ $issuer }}</option>
                                @endforeach
                            </select></span>
                        @endif
                    </label>
                @endforeach
            </div>
            <div class="shopwire-checkout-account">
                <h3>@lang('shopwire::cart.account')</h3>
                @auth(config('shopwire.auth_guard'))
                    @lang('shopwire::cart.logged_in_as') {{ auth()->user()->email }}
                    <button class="shopwire-checkout-button shopwire-checkout-button-logout" wire:click="logout">@lang('shopwire::cart.logout')</button>
                @else
                    <label class="shopwire-checkout-account-email">
                        <span>@lang('shopwire::cart.email')</span>
                        <input type="email" wire:model="form.email" placeholder="@lang('shopwire::cart.email')">
                    </label>
                    <label class="shopwire-checkout-account-radio">
                        <input type="radio" wire:model="account" value="login"><span></span> @lang('shopwire::cart.account_login')
                    </label>
                    <div class="{{ $account == 'login' ? 'shopwire-checkout-account-show' : 'shopwire-checkout-account-hide' }}">
                        <label class="shopwire-checkout-account-password">
                            <span>@lang('shopwire::cart.password')</span>
                            <input type="password" wire:model="form.password" placeholder="@lang('shopwire::cart.password')">
                        </label>
                        <button class="shopwire-checkout-button" wire:click="login">@lang('shopwire::cart.login')</button>
                        @if ($login_message)
                            <span class="shopwire-checkout-login-message">{{ $login_message }}</span>
                        @endif
                    </div>
                    <label class="shopwire-checkout-account-radio">
                        <input type="radio" wire:model="account" value="create"><span></span> @lang('shopwire::cart.account_create')
                    </label>
                    <div class="{{ $account == 'create' ? 'shopwire-checkout-account-show' : 'shopwire-checkout-account-hide'}}">
                        <label class="shopwire-checkout-account-password">
                            <span>@lang('shopwire::cart.password_choose')</span>
                            <input type="password" wire:model="password" placeholder="@lang('shopwire::cart.password_choose')">
                        </label>
                        <label class="shopwire-checkout-account-password">
                            <span>@lang('shopwire::cart.password_confirmation')</span>
                            <input type="password" wire:model="password_confirmation" placeholder="@lang('shopwire::cart.password_confirmation')">
                        </label>
                    </div>
                    <label class="shopwire-checkout-account-radio">
                        <input type="radio" wire:model="account" value="none"><span></span> @lang('shopwire::cart.account_none')
                    </label>
                @endif
            </label>
        @endforeach
    </div>
    <div class="shopwire-checkout-form">
        <h3>@lang('shopwire::cart.ship_to')</h3>
        @foreach($form_columns as $column => $attributes)
            @if (!$attributes['group'] || $form_groups[$attributes['group']])
                <label class="shopwire-checkout-form-{{ $attributes['type'] }}">
                    @if (isset($attributes['columns']) || $attributes['type'] == 'checkbox')
                        <input type="checkbox" wire:model="form.{{ $column }}"><span></span>
                    @endif
                    <span>{{ $attributes['label'] }}</span>
                    @if ($attributes['type'] == 'country')
                        <span class="shopwire-checkout-select"><select wire:model="form.{{ $column }}">
                            <option value="">@lang('shopwire::cart.select_country')</option>
                            @foreach($countries as $code => $country)
                                <option value="{{ $code }}">{{ $country }}</option>
                            @endforeach
                        </select></span>
                    @elseif ($attributes['type'] == 'textarea')
                        <textarea wire:model="form.{{ $column }}" rows="4" placeholder="{{ $attributes['label'] }}"></textarea>
                    @elseif (!isset($attributes['columns']) && $attributes['type'] != 'checkbox')
                        <input type="{{ $attributes['type'] }}" wire:model="form.{{ $column }}" placeholder="{{ $attributes['label'] }}">
                    @endif
                </label>
            @endif
        @endforeach
    </div>
</div>
