<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    protected $proxies;

    public function __construct()
    {
        $this->proxies = config('trustedproxy.proxies');
    }
}
