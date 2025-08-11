<?php
namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\JWTAuth;
use Clicalmani\Foundation\Http\RequestInterface;
use Clicalmani\Foundation\Http\ResponseInterface;

class Tokenizer extends JWTAuth 
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
        if (false !== $this->verifyToken($request->bearerToken())) return $next();
        
        return $response->unauthorized();
    }

    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot() : void
    {
        // Please create the auth.php file
        include_once routes_path('/auth.php');
    }
}
