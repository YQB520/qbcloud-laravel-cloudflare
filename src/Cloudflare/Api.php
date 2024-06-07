<?php

namespace QbCloud\Cloudflare\Cloudflare;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\SSL;
use Cloudflare\API\Endpoints\User;
use Cloudflare\API\Endpoints\Zones;

class Api
{
    public static function adapter(): Guzzle
    {
        $key = new APIKey(config('cloudflare.email'), config('cloudflare.global_key'));
        return new Guzzle($key);
    }

    public static function user(Guzzle $adapter): User
    {
        return new User($adapter);
    }

    public static function zone(Guzzle $adapter): Zones
    {
        return new Zones($adapter);
    }

    public static function dns(Guzzle $adapter): DNS
    {
        return new DNS($adapter);
    }

    public static function ssl(Guzzle $adapter): SSL
    {
        return new SSL($adapter);
    }
}
