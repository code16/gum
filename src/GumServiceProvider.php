<?php

namespace Code16\Gum;

use Illuminate\Support\ServiceProvider;

class GumServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(dirname(__DIR__) . '/database/migrations');
    }

    public function register()
    {
//        $this->app->register(SharpServiceProvider::class);
    }
}