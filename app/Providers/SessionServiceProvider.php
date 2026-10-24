<?php
namespace App\Providers;

use Clicalmani\Core\Providers\SessionStorageServiceProvider as SessionProvider;

class SessionServiceProvider extends SessionProvider
{
    protected static string $driver = \Clicalmani\Core\Http\Session\FileSessionHandler::class;

    /**
     * Here you may specify the number of seconds that you wish the session
     * to be allowed to remain idle before it expires.
     * 
     * @var int
     */
    protected static int $lifetime = 3000;

    /**
     * Here you may specify the maximum number of seconds that you wish the session
     * should be idle.
     * 
     * @var int
     */
    protected static int $max_lifetime = 9000;

    /**
     * If you want session to immediately expire on the browser closing, set that option.
     * 
     * @var bool
     */
    protected static bool $expire_on_close = false;

    /**
     * Map block defining browser tracking cookie policies and boundary visibility controls.
     * 
     * @var array
     */
    protected static array $cookie = [
        'name'      => '__SESSION_COOKIE__',
        'path'      => '/',
        'domain'    => '',
        'secure'    => false,
        'http_only' => true,
        'samesite'  => true
    ];
    
    /**
     * Bootstrap
     * 
     * @return void
     */
    public function boot(
        ?\Clicalmani\Core\Acme\Container $container = null
    ): void
    {
        parent::boot($container);
    }
}
