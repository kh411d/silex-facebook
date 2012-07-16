<?php
namespace Acme\Provider\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
		
		$controllers->match('/', function(Request $request) use ($app) {
		 echo "<pre>"; 
		 var_dump($app['campaign']->current());
		 
		 //var_dump($app['helper.facebook']->getAuthorizedUser());
		 //var_dump($app['helper.facebook']->isAppUser(730189516));
		 
		 $auth_button = $app['helper.facebook']->authorizeButton();

    	 return $app['twig']->render('home.html',array('auth_button' => $auth_button));
		
		})->method('GET|POST')
		  ->bind('home');

        return $controllers;
    }
}