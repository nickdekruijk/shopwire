<table width="100%">
    <tr>
        <th align="left" class="shopwire-checkout-product">@lang('shopwire::cart.product')</th>
        <th align="right" class="shopwire-checkout-price">@lang('shopwire::cart.price')</th>
        <th align="center" class="shopwire-checkout-quantity">@lang('shopwire::cart.quantity')</th>
        <th align="right" class="shopwire-checkout-total">@lang('shopwire::cart.total')</th>
    </tr>
    @foreach ($cart->items as $item)
        @if ($item->product_id)
            <tr>
                <td class="shopwire-checkout-product">{{ $item->title }}</td>
                <td align="right" class="shopwire-checkout-price">{{ Shopwire::money($item->price->price_including_vat) }}</td>
                <td align="center" class="shopwire-checkout-quantity">{{ $item->quantity }}</td>
                <td align="right" class="shopwire-checkout-total">{{ Shopwire::money($item->quantity * $item->price->price_including_vat) }}</td>
            </tr>
        @else
            <tr>
                <td colspan="3" class="shopwire-checkout-shipping">{{ $item->title }}</td>
                <td align="right" class="shopwire-checkout-shipping shopwire-checkout-total">{{ $item->price ? Shopwire::money($item->price->price) : '' }}</td>
            </tr>
        @endif
    @endforeach
    <tr>
        <td colspan="3" class="shopwire-checkout-subtotal">@lang('shopwire::cart.total_to_pay')</td>
        <td align="right" class="shopwire-checkout-subtotal">{{ Shopwire::money($cart->statistics['amount_including_vat']) }}</td>
    </tr>
</table>
