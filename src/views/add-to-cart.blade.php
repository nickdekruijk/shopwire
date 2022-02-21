<div class="shopwire-add">
    <div class="price">
        {{ Shopwire::money($product->price) }}
    </div>
    <label class="quantity">
        <span>@lang('shopwire::cart.quantity')</span>
        <input type="number" wire:model="quantity" min="{{ $cart_quantity ? 0 : 1 }}" size="3">
    </label>
    <div class="buttons">
        @if ($cart_quantity && $cart_quantity == $quantity)
            <a class="button" href="{{ config('shopwire.checkout_url') }}">@lang('shopwire::cart.checkout')</a>
        @elseif ($cart_quantity)
            <button class="button" wire:click="add">@lang('shopwire::cart.update_cart')</button>
        @else
            <button class="button" wire:click="add">@lang('shopwire::cart.add_to_cart')</button>
        @endif
    </div>
</div>
