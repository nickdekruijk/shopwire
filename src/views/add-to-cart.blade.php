<div class="shopwire-add">
    <div class="price">
        {{ Shopwire::money($product->price) }}
    </div>
    <label class="quantity">
        @lang('shopwire::cart.quantity')
        <input type="number" wire:model="quantity" min="1" size="3">
    </label>
    <button class="button" wire:click="add">@lang('shopwire::cart.add_to_cart')</button>
</div>
