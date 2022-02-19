<?php

namespace NickDeKruijk\Shopwire;

class ShopwireFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Name of the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'nickdekruijk-shopwire';
    }
}
