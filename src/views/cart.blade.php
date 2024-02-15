<a class="shopwire-cart shopwire-cart-{{ $count }}" href="{{ config('shopwire.checkout_url') }}">
    @isset($blade_icon)
        @svg($blade_icon, 'shopwire-cart-icon')
    @endif
    @if ($count)
        <span class="shopwire-cart-count">{{ $count }}</span>
    @else
        <span class="shopwire-cart-empty">@lang('shopwire::cart.empty')</span>  
    @endif
</a>
