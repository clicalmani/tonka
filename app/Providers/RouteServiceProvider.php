<?php
namespace App\Providers;

use Clicalmani\Core\Providers\RouteServiceProvider as ServiceProvider;
use Clicalmani\Core\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * API Routes prefix
     * 
     * @var string 
     */
    protected string $api_prefix = 'api';

    /**
     * Parameter prefix
     * 
     * @var string 
     */
    protected string $parameter_prefix = ':';

    /**
     * Defines route model bindings and pattern filters 
     * 
     * @return void
     */
    public function register() : void
    {
        $this->routes(function() {
            if ( Route::isApi() ) {
                Route::group(fn() => require_once root_path( $this->api_handler ) )
                    ->prefix( $this->api_prefix )
                    ->middleware('api');
            } elseif(Route::isBroadcasting()) {
                Route::group(function () {
                        require_once routes_path('channels.php');
                        require_once routes_path('broadcasting.php');
                    })
                    ->prefix(config('broadcasting.auth_prefix', 'broadcasting'))
                    ->middleware(\App\Http\Middlewares\Broadcasting::class);
            } 
            else {
                Route::group(fn() => require_once root_path( $this->web_handler ) )
                    ->middleware('web');
            }
        });
    }

    public function boot(
        ?\Clicalmani\Core\Acme\Container $container = null
    ): void
    {
        /**
         * Global patterns
         */
        Route::pattern('id', '[0-9]+');
    }
}
