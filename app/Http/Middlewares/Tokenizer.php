<?php
namespace App\Http\Middlewares;

use Clicalmani\Foundation\Http\Middlewares\JWTAuth;
use Clicalmani\Foundation\Http\Request;
use Clicalmani\Foundation\Http\Response;

class Tokenizer extends JWTAuth 
{
    /**
     * Handler
     * 
     * @param Request $request Current request object
     * @param Response $response Http response
     * @param callable $next 
     * @return int|false
     */
    public function handle(Request $request, Response $response, callable $next) : int|false
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
        $this->container->inject(fn() => routes_path('/auth.php'));
    }
}
