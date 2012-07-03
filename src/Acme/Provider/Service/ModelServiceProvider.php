<?php

namespace Acme\Provider\Service;

use Silex\ServiceProviderInterface;
use Silex\Application;

class ModelServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->before(function() use ($app) {
            foreach ($app['model.models'] as $label => $class) {
                $app[$label] = $app->share(function($app) use ($class) {
                    return new $class($app['db']); 
                });
            }
        });
    }

    public function boot(Application $app)
    {
    }
}