<a class="shopwire-cart" href="{{ config('shopwire.checkout_url') }}">
    @if ($count)
        <span class="shopwire-count">{{ $count }}</span>
    @else
        <span class="shopwire-empty"></span>  
    @endif
</a>
