<?php
namespace Acme\Provider\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
		
		/** Home **/

		$controllers->match('/', function(Request $request) use ($app) {
		 echo "<pre>"; 
		 var_dump($app['campaign']->current());
		 
		 //var_dump($app['helper.facebook']->getAuthorizedUser());
		 //var_dump($app['helper.facebook']->isAppUser(730189516));
		 
		 $auth_button = $app['helper.facebook']->authorizeButton();

    	 return $app['twig']->render('home.html',array('auth_button' => $auth_button));
		
		})->method('GET|POST')
		  ->bind('home');


		/** Page **/  

		$controllers->match('/page/{id}', function(Request $request,$id) use ($app) {
		  
		  if($page = $app['page']->getById($id)){
		  	return $app['twig']->render('page.html',array('page' => $page));
		  }else{
		  	$app->abort(404, "Page does not exist.");
		  } 
		
		})->method('GET|POST')
		  ->bind('page');		  

        
        /** Register **/

		$controllers->match('/register', function(Request $request) use ($app) {
		
		//Validation
		if(!$campaign = $app['campaign']->current()){
			$app->abort(404,"Page does not exists.");
		}

		if($campaign['on_judging']){
		    $data['title'] = "The Winner Announce Soon";
			$data['text'] = "Sorry! We are on Judging Time for The Campaign.";
			return $app['twig']->render('notification.html',$data);
	 	}

	 	$sr = $app['facebook']->getSignedRequest();
	 	$redirect_url = isset($sr['page']) ? '' : "/register";
	
	 
		 if(!$user = $app['helper.facebook']->getAuthorizedUser(true)){
		 	return $app->redirect($app['url_generator']->generate('authorize').'?ref='.$redirect_url);
		 }
		 
		 if(!$isFan = $app['helper.facebook']->user_isFan()){
		 	return $app->redirect($app['url_generator']->generate('likepage').'?ref='.$redirect_url);
		 }
		 
		 if($app['customer']->isRegistered($user['id'])){
		 	return $app->redirect($app['url_generator']->generate('home'));
		 }

	 	//Form Builder
		 $builder = $app['form.factory']->createBuilder('form',$data_default);
			$form = $builder
					->add('name', 'text', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'Isi nama anda'),
						'label'		  => 'Nama'
					))
					->add('email', 'text', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'Isi email anda'),
						'label'		  => 'Email'
					))
					->add('address', 'textarea', array(
						'attr'        => array('placeholder' => 'isi alamat'),
						'label'		  => 'Alamat'
					))
					->add('phone', 'text', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'Isi No Telpon anda'),
						'label'		  => 'Telepon.'
					))
					->getForm();
    	 
		if ('POST' === $app['request']->getMethod()) {
				$form->bindRequest($app['request']);
				if ($form->isValid()) {
					 $values = $form->getData();
					 $values['regdate'] = \date('Y-m-d H:i:s');
					 $values['fb_uid'] = $user['id'];
					 $result = $app['customer']->add($values);
					 
					return $app->redirect($app['url_generator']->generate('upload'));
				} else {
					$form->addError(new FormError('Maaf, silahkan coba kembali.'));
					return $app->redirect($app['url_generator']->generate('register'));
				}
		}

    	 return $app['twig']->render('register.html',array('form'=>$form->createView()));
		
		})->method('GET|POST')
		  ->bind('register');
		/**/  



        /** Upload **/

		$controllers->match('/upload', function(Request $request) use ($app) {
		 //Validation
		if(!$campaign = $app['campaign']->current()){
			$app->abort(404,"Page does not exists.");
		}

		if($campaign['on_judging']){
		    $data['title'] = "The Winner Announce Soon";
			$data['text'] = "Sorry! We are on Judging Time for The Campaign.";
			return $app['twig']->render('notification.html',$data);
	 	}

	 	if(!$campaign['on_upload']){
		   	$data['title'] = "Campaign Upload End";
			$data['text'] = "Sorry! Upload submission for the campaign has just ended";
			return $app['twig']->render('notification.html',$data);
	 	}

	 	$sr = $app['facebook']->getSignedRequest();
	 	$redirect_url = isset($sr['page']) ? '' : "/register";
	
	 
		 if(!$user = $app['helper.facebook']->getAuthorizedUser(true)){
		 	return $app->redirect($app['url_generator']->generate('authorize').'?ref='.$redirect_url);
		 }
		 
		 if(!$isFan = $app['helper.facebook']->user_isFan()){
		 	return $app->redirect($app['url_generator']->generate('likepage').'?ref='.$redirect_url);
		 }
		 
		 if(!$app['customer']->isRegistered($user['id'])){
		 	return $app->redirect($app['url_generator']->generate('register'));
		 }
		 
		 //Form Builder
		 $builder = $app['form.factory']->createBuilder('form',$data_default);
			$form = $builder
					->add('summary', 'textarea', array(
						'attr'        => array('placeholder' => 'Fill your shout!'),
						'label'		  => 'Your Shout'
					))
					->getForm();
    	 
		if ('POST' === $app['request']->getMethod()) {
				$form->bindRequest($app['request']);
				if ($form->isValid()) {
					 $values = $form->getData();
					 $values['submitdate'] = \date('Y-m-d H:i:s');
					 $values['campaign_id'] = $campaign['campaign_id'];
					 $values['customer_id'] = $app['customer']->getById($user['id'],'fbuid');
					 $result = $app['customeritem']->add($values);
					 
					return $app->redirect($app['url_generator']->generate('upload'));
				} else {
					$form->addError(new FormError('Maaf, silahkan coba kembali.'));
					return $app->redirect($app['url_generator']->generate('upload'));
				}
		}

    	 
    	 return $app['twig']->render('upload.html',array());
		
		})->method('GET|POST')
		  ->bind('upload');
		/**/  



        /** xxx **

		$controllers->match('/', function(Request $request) use ($app) {
		
    	 
    	 return $app['twig']->render('.html',array());
		
		})->method('GET|POST')
		  ->bind('');
		/**/  



        /** xxx **

		$controllers->match('', function(Request $request) use ($app) {
		
    	 
    	 return $app['twig']->render('.html',array());
		
		})->method('GET|POST')
		  ->bind('');
		/**/  


        /** xxx **

		$controllers->match('', function(Request $request) use ($app) {
		
    	 
    	 return $app['twig']->render('.html',array());
		
		})->method('GET|POST')
		  ->bind('');
		/**/  



        /** xxx **

		$controllers->match('', function(Request $request) use ($app) {
		
    	 
    	 return $app['twig']->render('.html',array());
		
		})->method('GET|POST')
		  ->bind('');
		/**/  


        return $controllers;
    }
}