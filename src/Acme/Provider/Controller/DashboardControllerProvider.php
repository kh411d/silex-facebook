<?php
namespace Acme\Provider\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

class DashboardControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

		$controllers->match('/', function(Request $request) use ($app) {
		
		
		  return $app['twig']->render('dashboard/home.html');
	
		})->method('GET|POST')
		  ->bind('dashboard_home');
		
		/* Campaign Index */
		
		$controllers->match('/campaign/', function(Request $request) use ($app) {
		    /*  $campaign = $app['campaign'];
			//Action
			if(isset($_POST['action']) && $act = addslashes($_POST['action'])){
			 $cid = @$_POST['cid'];
			 switch($act){
			   case "activate":
				  if(@$cid)
				   foreach($cid as $v)
				   $campaign->setStatus($v,'active');
			   break;
			   case "deactivate":
				 if(@$cid)
				   foreach($cid as $v)
				   $campaign->setStatus($v,'inactive');
			   break;
			 }
			}
			
			//Filter
			$filter = array();
			$filter_vars = array();
			if(($filter_key = addslashes(@$_REQUEST['filter_key'])) &&  ($filter_value = addslashes(@$_REQUEST['filter_value']))){
			  switch($filter_key){
				case "title": $filter['title'] = array("LIKE" => "'%$filter_value%'"); break;
				case "onDate": $filter['startdate'] = array("<=" => "'$filter_value'");
							   $filter['endate'] = array(">=" => "'$filter_value'");
							   break;
				case "status": $filter['status'] = "'$filter_value'"; break;
			  }

			  $filter_vars = array('filter_key'=>@$_REQUEST['filter_key'],
									'filter_value'=>@$_REQUEST['filter_value']);
			}
			
			//Pagination Setup
			 list($t) = $campaign->retrieve($filter,array('fields'=>'count(*) as total_items'));
			 $total_items = $t['total_items'];
			
			 $perPage = 10;   
			 extract(pagination($total_items,$perPage,$filter_vars));	
			
			//Set Params
			 $params['filter_key'] = @$_REQUEST['filter_key'];
			 $params['filter_value'] = @$_REQUEST['filter_value'];
			 $params['paginate'] = $paginate_links; 
			 $params['offset'] = $offset;
			 $params['data'] = $campaign->retrieve($filter,array('limit_number'=>$perPage,'limit_offset'=>$offset));
		 */
		  return $app['twig']->render('/dashboard/campaign_index.html');
	
		})->method('GET|POST')
		  ->bind('dashboard_campaign_home');  
		  
		/********************/
		$controllers->match('/campaign/addedit', function(Request $request) use ($app) {
		   $builder = $app['form.factory']->createBuilder('form');
			$choices = array('choice a', 'choice b', 'choice c');

			$form = $builder
				->add(
					$builder->create('sub-form', 'form')
						->add('subformemail1', 'email', array(
							'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
							'attr'        => array('placeholder' => 'email constraints')
						))
						->add('subformtext1', 'text')
				)
				->add('text1', 'text', array(
					'constraints' => new Assert\NotBlank(),
					'attr'        => array('placeholder' => 'not blank constraints')
				))
				->add('text2', 'text', array('attr' => array('class' => 'span1', 'placeholder' => '.span1')))
				->add('text3', 'text', array('attr' => array('class' => 'span2', 'placeholder' => '.span2')))
				->add('text4', 'text', array('attr' => array('class' => 'span3', 'placeholder' => '.span3')))
				->add('text5', 'text', array('attr' => array('class' => 'span4', 'placeholder' => '.span4')))
				->add('text6', 'text', array('attr' => array('class' => 'span5', 'placeholder' => '.span5')))
				->add('text8', 'text', array('disabled' => true, 'attr' => array('placeholder' => 'disabled field')))
				->add('textarea', 'textarea')
				->add('email', 'email')
				->add('integer', 'integer')
				->add('money', 'money')
				->add('number', 'number')
				->add('password', 'password')
				->add('percent', 'percent')
				->add('search', 'search')
				->add('url', 'url')
				->add('choice1', 'choice',  array(
					'choices'  => $choices,
					'multiple' => true,
					'expanded' => true
				))
				->add('choice2', 'choice',  array(
					'choices'  => $choices,
					'multiple' => false,
					'expanded' => true
				))
				->add('choice3', 'choice',  array(
					'choices'  => $choices,
					'multiple' => true,
					'expanded' => false
				))
				->add('choice4', 'choice',  array(
					'choices'  => $choices,
					'multiple' => false,
					'expanded' => false
				))
				->add('datetime', 'datetime')
				->add('time', 'time')
				->add('birthday', 'birthday')
				->add('checkbox', 'checkbox')
				->add('file', 'file')
				->add('radio', 'radio')
				->add('password_repeated', 'repeated', array(
					'type'            => 'password',
					'invalid_message' => 'The password fields must match.',
					'options'         => array('required' => true),
					'first_options'   => array('label' => 'Password'),
					'second_options'  => array('label' => 'Repeat Password'),
				))
				->getForm()
			;

			if ('POST' === $app['request']->getMethod()) {
				$form->bindRequest($app['request']);
				if ($form->isValid()) {
					$app['session']->setFlash('success', 'The form is valid');
				} else {
					$form->addError(new FormError('This is a global error'));
					$app['session']->setFlash('info', 'The form is bind, but not valid');
				}
			}
			 return $app['twig']->render('/dashboard/campaign_addedit.html',array('form'=>$form->createView()));
		})->method('GET|POST')
		  ->bind('dashboard_campaign_addedit');  
		/********************/

        return $controllers;
    }
}