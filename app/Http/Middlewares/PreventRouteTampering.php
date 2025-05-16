<?php
namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\Response;

class PreventRouteTampering extends Middleware 
{
    /**
     * Handler
     * 
     * @param Request $request Current request object
     * @param Response $response Http response
     * @param callable $next 
     * @return \Clicalmani\Foundation\Http\Response|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(Request $request, Response $response, callable $next) : \Clicalmani\Foundation\Http\Response|\Clicalmani\Foundation\Http\RedirectInterface
    {
        if (!!$request->hash && false == $request->verifyParameters()) {
            return $response->unauthorized();
        }

        return $next();
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        // ...
    }
}
