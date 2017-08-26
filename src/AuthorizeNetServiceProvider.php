<?php

namespace Pseudocody\AuthorizeNet;

use Illuminate\Support\ServiceProvider;

class AuthorizeNetServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->app->bind(AuthorizeNet::class, function () {
            return new AuthorizeNet(config('authorizenet.login_id'), config('authorizenet.transaction_key'));
        });
        $this->publishes([
            __DIR__ . '/../config/authorizenet.php' => config_path('authorizenet.php'),
        ]);
    }

    public function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/authorizenet.php' => config_path('authorizenet.php')
        ], 'config');
    }

    public function register()
    {
        $this->app->bind('authorizenet', function () {
            return $this->app->make(AuthorizeNet::class);
        });
        $this->mergeConfigFrom(__DIR__ . '/../config/authorizenet.php', 'authorizenet');
    }
}