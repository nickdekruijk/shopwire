<div class="shopwire-add">
    <div class="price">
        {{ Shopwire::money($price) }}
    </div>
    @if ($options && !$option_fixed)
        <label class="option">
            <span>@lang('shopwire::cart.option')</span>
            <select wire:model.live="option_id">
                <option value="">@lang('shopwire::cart.select_option')</option>
                @foreach ($options as $option)
                    <option value="{{ $option['id'] }}" @disabled($option['sold_out'])>{{ $option['title'] }}{{ $option['sold_out'] ? ' (' . __('shopwire::cart.out_of_stock') . ')' : '' }}</option>
                @endforeach
            </select>
            @error('option_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>
    @endif
    <label class="quantity">
        <span>@lang('shopwire::cart.quantity')</span>
        <input type="number" wire:model="quantity" min="{{ $cart_quantity ? 0 : 1 }}" size="3">
        @error('quantity')
            <span class="error">{{ $message }}</span>
        @enderror
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
