<?php

namespace QbCloud\Cloudflare\Providers;

use Illuminate\Support\ServiceProvider;
use QbCloud\Cloudflare\Cloudflare;

class CloudflareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cloudflare', function ($app) {
            return new Cloudflare();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__ . '/../../config/cloudflare.php');

        $this->publishes([$path => config_path('cloudflare.php')], 'config');

        $this->mergeConfigFrom($path, 'cloudflare');
    }
}
