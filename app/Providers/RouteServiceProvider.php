<?php
namespace App\Providers;

use Clicalmani\Foundation\Providers\RouteServiceProvider as ServiceProvider;
use Clicalmani\Foundation\Routing\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * API Routes prefix
     * 
     * @var string 
     */
    protected $api_prefix = 'api';

    /**
     * Parameter prefix
     * 
     * @var string 
     */
    protected $parameter_prefix = ':';

    /**
     * Defines route model bindings and pattern filters 
     * 
     * @return void
     */
    public function boot() : void
    {
        $this->routes(function() {
            if ( Route::isApi() ) {
                Route::group(fn() => require_once root_path( $this->api_handler ) )
                    ->prefix( $this->api_prefix )
                    ->middleware('api');
            } else require_once root_path( $this->web_handler );
        });
    }

    public function register(): void
    {
        ServiceProvider::responseHandler(fn(mixed $user) => [
            // Data
        ]);

        /**
         * Global patterns
         */
        // Route::pattern('id', '[0-9]+');
    }
}
