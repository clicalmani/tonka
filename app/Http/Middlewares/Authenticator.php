<?php
namespace App\Http\Middlewares;

use Clicalmani\Core\Http\Middlewares\Middleware;
use Clicalmani\Core\Http\RequestInterface;
use Clicalmani\Core\Http\ResponseInterface;

class Authenticator extends Middleware
{
    /**
     * Handler
     * 
     * @param \Clicalmani\Core\Http\RequestInterface $request Request object
     * @param \Clicalmani\Core\Http\ResponseInterface $response Response object
     * @param \Closure $next Next middleware function
     * @return \Clicalmani\Core\Http\ResponseInterface|\Clicalmani\Core\Http\RedirectInterface
     */
    public function handle(RequestInterface $request, ResponseInterface $response, \Closure $next) : \Clicalmani\Core\Http\ResponseInterface|\Clicalmani\Core\Http\RedirectInterface
    {
        if ($user = $request->user()) {
            if ($user->isAuthenticated() && false === $user->isOnline()) {
                $user->destroy();
                return redirect()->route('home');
            }

            $user->authenticate(); // Sliding expiration

            return $next();
        }

        return redirect()->route('home');
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        $this->include('auth');
    }
}
