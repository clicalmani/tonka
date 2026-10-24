<?php
return [

    /**
     * |--------------------------------------------------------------------
     * |                Authentication Service Provider
     * |--------------------------------------------------------------------
     * 
     * User authenticator
     */
    'auth' => [
        'user' => App\Providers\AuthServiceProvider::class
    ],

    /**
     * |-------------------------------------------------------------------
     * | Redirect Service
     * |-------------------------------------------------------------------
     * 
     */
    'tps' => [
        // 'maintenance' => App\Auth\MaintenanceRedirect::class,
        // 404 => App\Auth\MaintenanceRedirect::class,
    ],

    'notifications' => [
        // Clicalmani\Notification\Channels\BroadcastChannel::class,
        // Clicalmani\Notification\Channels\DatabaseChannel::class,
        // Clicalmani\Notification\Channels\EmailChannel::class,
    ],

    /**
     * |-------------------------------------------------------------------
     * |                    Console Commands
     * |-------------------------------------------------------------------
     * 
     */
    'commands' => [
        // ...
    ],
];
