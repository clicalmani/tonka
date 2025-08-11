<?php

/**
 * |-------------------------------------------------------------------------------
 * | Application Bootstrap File
 * |-------------------------------------------------------------------------------
 * This file is responsible for bootstrapping the application.
 * It sets up the application instance, loads necessary services, and runs the application.
 * 
 * @package Clicalmani\Foundation
 * @author Clicalmani
 * @version 2.3.4
 * @link https://github.com/clicalmani/foundation
 */


use Clicalmani\Foundation\Maker\Application;
use Symfony\Component\DependencyInjection\Loader\Configurator\DefaultsConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;

return Application::setup(rootPath: dirname(__DIR__))
            ->withService(static function(Application $app) {
                $app->addService('smtp.mailer.transport', [\Clicalmani\Foundation\Mail\MailerTransport::class]);
                $app->addService(
                    'smtp.mailer', 
                    [
                        \Clicalmani\Foundation\Mail\Mailer::class,
                        static fn(ServiceConfigurator|DefaultsConfigurator $config) => $config->args([$app->dependency('service', 'smtp.mailer.transport')])
                    ]);
            })
            ->run();
