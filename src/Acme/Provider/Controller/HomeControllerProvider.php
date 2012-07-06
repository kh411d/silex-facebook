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
		    $data = array(
        'name' => 'Your name',
        'email' => 'Your email',
    );
		
			$form = $app['form.factory']->createBuilder('form', $data)
			->add('name')
			->add('email')
			->add('gender', 'choice', array(
				'choices' => array(1 => 'male', 2 => 'female'),
				'expanded' => true,
			))
			->getForm();

			if ('POST' == $request->getMethod()) {
				$form->bindRequest($request);

				if ($form->isValid()) {
					$data = $form->getData();

					// do something with the data

					// redirect somewhere
					return $app->redirect('...');
				}
			}
			
			//$app['epi.template']->display('home.php',array('customer'=>$app['customer']->get_all(),
			//												'form'=>$form->createView())); 
	// display the form
	$customer = $app['customer']->get_all();

    return $app['twig']->render('home.html', array('customer' => $customer,'form'=>$form->createView()));
		
		})->method('GET|POST')
		  ->bind('home');

        return $controllers;
    }
}