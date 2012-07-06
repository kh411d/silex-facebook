<?php
namespace Acme\Provider\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
		
		$controllers->match('/{id}', function(Request $request) use ($app) {
		
		})->method('GET|POST')
		  ->bind('home');

        return $controllers;
    }
}