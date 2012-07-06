<?php

namespace Acme\Provider\Service;

use Silex\ServiceProviderInterface;
use Silex\Application;

class EpiTemplateServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->before(function() use ($app) {
			$app['epi.template'] = $app->share(function($app){
                    return new \Acme\Lib\EpiTemplate($app['epi.template.path']); 
            });
        });
    }

    public function boot(Application $app)
    {
    }
}