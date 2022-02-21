<a class="shopwire-cart shopwire-cart-{{ $count }}" href="{{ config('shopwire.checkout_url') }}">
    @if ($count)
        <span class="shopwire-cart-count">{{ $count }}</span>
    @else
        <span class="shopwire-cart-empty">@lang('shopwire::cart.empty')</span>  
    @endif
</a>
