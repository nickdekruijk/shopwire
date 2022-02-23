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
                                <div class="select"><select wire:model="shipping">
                                    <option value="">@lang('shopwire::cart.select-shipping')</option>
                                    @foreach($shipping_options as $shipping_rate)
                                        <option value="{{ $shipping_rate['id'] }}">{{ $shipping_rate['title'] }}</option>
                                    @endforeach
                                </select></div>
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
                <td colspan="3"><label><input type="checkbox" wire:model="includingVat">@lang('shopwire::cart.vat_toggle')</label></td>
            </tr>
        </table>
    </div>
    <div class="shopwire-checkout-form">
        <h3>@lang('shopwire::cart.ship_to')</h3>
        @foreach($form_columns as $column => $attributes)
            @if (!$attributes['group'] || $form_groups[$attributes['group']])
                <label class="shopwire-checkout-form-{{ $attributes['type'] }}">
                    @if (isset($attributes['columns']) || $attributes['type'] == 'checkbox')
                        <input type="checkbox" wire:model="form.{{ $column }}" placeholder="{{ $attributes['label'] }}">
                    @endif
                    <span>{{ $attributes['label'] }}</span>
                    @if ($attributes['type'] == 'country')
                        <div class="select"><select wire:model="form.{{ $column }}">
                            <option value="">@lang('shopwire::cart.select_country')</option>
                            @foreach($countries as $code => $country)
                                <option value="{{ $code }}">{{ $country }}</option>
                            @endforeach
                        </select></div>
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
