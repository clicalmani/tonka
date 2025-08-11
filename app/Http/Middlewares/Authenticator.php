<?php
namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\Middleware;
use Clicalmani\Foundation\Http\RequestInterface;
use Clicalmani\Foundation\Http\ResponseInterface;

class Authenticator extends Middleware 
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Foundation\Http\Requests\RequestInterface $request Request object
     * @param \Clicalmani\Foundation\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : \Clicalmani\Foundation\Http\ResponseInterface|\Clicalmani\Foundation\Http\RedirectInterface
    {
        if ($user = $request->user()) {
            if (false === $user->isOnline()) return redirect()->route('login');
            else $user->authenticate(); // Renew user authentication
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
        /**
         * TODO
         */
    }
}
