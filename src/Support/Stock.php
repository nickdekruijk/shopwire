<?php

namespace NickDeKruijk\Shopwire\Support;

use NickDeKruijk\Shopwire\Models\Cart;

/**
 * Decides where the stock of a product or product option is kept, so checking
 * the stock (prevent_oversell) and decreasing it (decrease_stock) always look
 * at the same column.
 */
final class Stock
{
    /**
     * Return where the stock is kept as [model class, column, key], or null
     * when no stock column is configured.
     *
     * The stock of a product option is used when the option has a stock
     * column configured, otherwise the stock of the product itself.
     */
    public static function target($product_id, $product_option_id = null): ?array
    {
        $optionModel = config('shopwire.product_option_model');
        $optionColumn = config('shopwire.product_option_columns.stock');
        if ($product_option_id && $optionModel && $optionColumn) {
            return [$optionModel, $optionColumn, $product_option_id];
        }

        $productModel = config('shopwire.product_model');
        $productColumn = config('shopwire.product_columns.stock');
        if ($productModel && $productColumn) {
            return [$productModel, $productColumn, $product_id];
        }

        return null;
    }

    /**
     * Return how many can be put in the cart, or null when the stock is
     * unlimited (no stock column, or a null stock value).
     *
     * When the stock is kept on the product, other options of the same
     * product in the cart use the same stock and are subtracted.
     */
    public static function available($product_id, $product_option_id = null, ?Cart $cart = null): ?float
    {
        $target = self::target($product_id, $product_option_id);
        if (!$target) {
            return null;
        }

        [$model, $column, $key] = $target;
        $stock = (new $model)->newQueryWithoutScopes()->find($key)?->getAttribute($column);
        if ($stock === null) {
            return null;
        }
        $stock = (float) $stock;

        if ($cart && $model === config('shopwire.product_model')) {
            $stock -= (float) $cart->items()
                ->where('product_id', $product_id)
                ->where(function ($query) use ($product_option_id) {
                    if ($product_option_id) {
                        $query->where('product_option_id', '!=', $product_option_id)->orWhereNull('product_option_id');
                    } else {
                        $query->whereNotNull('product_option_id');
                    }
                })
                ->sum('quantity');
        }

        return max($stock, 0.0);
    }
}
