<?php

namespace QbCloud\Cloudflare\Facades;

use Illuminate\Support\Facades\Facade;

class Cloudflare extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cloudflare';
    }
}
