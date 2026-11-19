<?php

use NickDeKruijk\Shopwire\Support\Money;

it('converts between decimals and cents without float noise', function () {
    expect(Money::toCents('12.30'))->toBe(1230)
        ->and(Money::toCents(0.1 + 0.2))->toBe(30)
        ->and(Money::toCents(null))->toBe(0)
        ->and(Money::toCents(12.345))->toBe(1235)
        ->and(Money::fromCents(1230))->toBe(12.3);
});

it('calculates VAT in whole cents', function () {
    expect(Money::excludingVat(1210, 21))->toBe(1000)
        ->and(Money::includingVat(1000, '21.00'))->toBe(1210)
        ->and(Money::excludingVat(1000, 9))->toBe(917)
        ->and(Money::excludingVat(1000, 0))->toBe(1000);
});

it('multiplies and takes percentages', function () {
    expect(Money::multiply(10, 3))->toBe(30)
        ->and(Money::multiply(333, '1.5'))->toBe(500)
        ->and(Money::percentage(1999, 10))->toBe(200);
});
