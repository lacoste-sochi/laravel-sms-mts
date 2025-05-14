<?php

namespace Rutrue\MtsSms\Facades;

use Illuminate\Support\Facades\Facade;

class MtsSms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mts-sms';
    }
}