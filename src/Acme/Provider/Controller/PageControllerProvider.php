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
		
		$controllers->match('/{id}', function(Request $request,$id) use ($app) {
		  
		  if($page = $app['page']->getById($id)){
		  	return $app['twig']->render('page.html',array('page' => $page));
		  }else{
		  	$app->abort(404, "Page does not exist.");
		  } 
		
		})->method('GET|POST')
		  ->bind('page');

        return $controllers;
    }
}