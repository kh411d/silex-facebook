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
		
		$controllers->match('/campaign', function(Request $request) use ($app) {
		     $campaign = $app['campaign'];
			//Action
			if($cid = $app['request']->query->get('activate')){
				$campaign->setStatus($cid,'active');
			}elseif($cid = $app['request']->query->get('postpone')){
				$campaign->setStatus($cid,'pending');
			}
			
			//Filter
			$filter = array();
			$filter_vars = array();
		
			
			//Pagination Setup
			 list($t) = $campaign->retrieve($filter,array('fields'=>'count(*) as total_items'));
			 $total_items = $t['total_items'];
			
			 $perPage = 2;   
			 $path = $app['url_generator']->generate('dashboard_campaign_home');
			 extract(\pagination($total_items,$perPage,$filter_vars,$path));	

			//Set Params
			 $params['paginate'] = $paginate_links; 
			 $params['offset'] = $offset;
			 $params['data'] = $campaign->retrieve($filter,array('limit_number'=>$perPage,'limit_offset'=>$offset));
		   
		  return $app['twig']->render('/dashboard/campaign_index.html',$params);
	
		})->method('GET|POST')
		  ->bind('dashboard_campaign_home');  
		  


		$controllers->match('/campaign/addedit', function(Request $request) use ($app) {
			$campaign = $app['campaign'];	
         $action = $app['request']->query->get('edit') ? 'edit' : 'add';
		 
		 if($action == 'edit'){
		  $db = $campaign->getById($app['request']->query->get('edit')); 
		  $data_default['campaign_id'] = $db['campaign_id'];
		  $data_default['action'] = $action;
		  $data_default['title'] = $db['title'];
		  $data_default['startdate_input'] = new \DateTime($db['startdate']);
		  $data_default['upload_enddate_input'] = new \DateTime($db['upload_enddate']);
		  $data_default['selectiondate_input'] = new \DateTime($db['selectiondate']);
		  $data_default['enddate_input'] = new \DateTime($db['enddate']);
		  $data_default['status'] = $db['status'];
		 }else{
		  $data_default = array('action'=>$action);	
		 }
		 
		 $builder = $app['form.factory']->createBuilder('form',$data_default);
			$form = $builder
					->add('action','hidden')
					->add('campaign_id','hidden')
					->add('title', 'text', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'not blank constraints'),
						'label'		  => 'Title'
					))
					->add('startdate_input', 'datetime',array('label' => 'Start Date'))
					->add('upload_enddate_input', 'datetime',array('label' => 'Submit End Date'))
					->add('selectiondate_input', 'datetime',array('label' => 'Selection Date'))
					->add('enddate_input', 'datetime',array('label' => 'End Date'))
					->add('status', 'choice',  array(
						'choices'  => array('pending'=>'pending','active'=>'active')
						))
					->getForm();
				
				
			if ('POST' === $app['request']->getMethod()) {
				$form->bindRequest($app['request']);
				if ($form->isValid()) {
					 $values = $form->getData();
					 $dbdata['title'] = $values['title'];
					 $dbdata['startdate'] = $values['startdate_input']->format('Y-m-d H:i:s');
					 $dbdata['upload_enddate'] = $values['upload_enddate_input']->format('Y-m-d H:i:s');
					 $dbdata['selectiondate'] = $values['selectiondate_input']->format('Y-m-d H:i:s');
					 $dbdata['enddate'] = $values['enddate_input']->format('Y-m-d H:i:s');
					 $dbdata['status'] = $values['status'];
					 if($values['action'] == 'edit'){
						$dbdata['campaign_id'] = $values['campaign_id'];
						$resultID = $campaign->update($dbdata);
					 }else{
						$resultID = $campaign->add($dbdata);
					 }
					  $app['session']->setFlash('success', 'Submission Succeed');
					return $app->redirect($app['url_generator']->generate('dashboard_campaign_addedit_success',array('id'=>$resultID)));
				} else {
					$form->addError(new FormError('This is a global error'));
					 $app['session']->setFlash('success', 'Submission Failed');
				}
			} 
			 return $app['twig']->render('/dashboard/campaign_addedit.html',array('form'=>$form->createView()));
		})->method('GET|POST')
		  ->bind('dashboard_campaign_addedit');  
		/********************/
		
		$controllers->match('/campaign/addedit/success/{id}', function(Request $request,$id) use ($app) {
		    
			return $app['twig']->render('/dashboard/addedit_success.html',array('addURL' => $app['url_generator']->generate('dashboard_campaign_addedit'),
																				'editURL' => $app['url_generator']->generate('dashboard_campaign_addedit')."?edit=$id"));
		
		})->method('GET|POST')
		  ->bind('dashboard_campaign_addedit_success')->value('id', null);  
		

        return $controllers;
    }
}