<?php

namespace NickDeKruijk\Shopwire\Tests\Fixtures;

use Illuminate\Mail\Mailable;
use RuntimeException;

class ThrowingMailable extends Mailable
{
    public function __construct($order)
    {
        throw new RuntimeException('SMTP unavailable');
    }
}
