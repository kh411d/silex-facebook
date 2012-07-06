<?php

namespace Acme\Provider\Service;

use Silex\ServiceProviderInterface;
use Silex\Application;

class HelperServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->before(function() use ($app) {
            foreach ($app['helper.helpers'] as $label => $class) {
                $app['helper.'.$label] = $app->share(function($app) use ($class) {
                    return new $class($app); 
                });
            }
        });
    }

    public function boot(Application $app)
    {
    }
}