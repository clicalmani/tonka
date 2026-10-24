<?php
namespace App\Providers;

use Clicalmani\Core\Providers\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register
     * 
     * @return void
     */
    public function register() : void
    {
        // ...
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot(
        ?\Clicalmani\Core\Acme\Container $container = null
    ): void
    {
        // ...
    }
}
