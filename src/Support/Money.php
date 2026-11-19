<?php

namespace NickDeKruijk\Shopwire\Support;

/**
 * Exact money arithmetic using integer cents.
 *
 * Amounts are still stored and exposed as decimals, but all calculations go
 * through whole cents so sums, VAT and discounts never drift by a fraction
 * of a cent due to floating point errors (e.g. 0.1 + 0.2 = 0.30000000000000004).
 * Rounding is half away from zero, the same as PHP's round().
 */
final class Money
{
    /**
     * Convert a decimal amount (float, numeric string or null) to cents.
     */
    public static function toCents(int|float|string|null $amount): int
    {
        return self::round((float) $amount * 100);
    }

    /**
     * Convert cents back to a decimal amount with at most two decimals.
     */
    public static function fromCents(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * Multiply an amount in cents by a (possibly fractional) quantity.
     */
    public static function multiply(int $cents, int|float|string $quantity): int
    {
        return self::round($cents * (float) $quantity);
    }

    /**
     * Calculate a percentage of an amount in cents.
     */
    public static function percentage(int $cents, int|float|string|null $percentage): int
    {
        return self::round($cents * (float) $percentage / 100);
    }

    /**
     * Return the amount excluding VAT for an amount including VAT.
     */
    public static function excludingVat(int $cents, int|float|string|null $rate): int
    {
        return self::round($cents / (1 + (float) $rate / 100));
    }

    /**
     * Return the amount including VAT for an amount excluding VAT.
     */
    public static function includingVat(int $cents, int|float|string|null $rate): int
    {
        return self::round($cents * (1 + (float) $rate / 100));
    }

    /**
     * Round to whole cents. The value is first rounded to 6 decimals to absorb
     * floating point noise, so 1234.4999999999 is treated as 1234.5 and becomes 1235.
     */
    private static function round(float $value): int
    {
        return (int) round(round($value, 6));
    }
}
