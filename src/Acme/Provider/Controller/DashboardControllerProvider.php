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
		
		
		
		$controllers->match('/customer/', function(Request $request) use ($app) {
		     $campaign = $app['campaign'];
			 $customer = $app['customer'];
			
			//Filter
			$filter = array();
			$filter_vars = array();
		
			
			//Pagination Setup
			 list($t) = $customer->retrieve($filter,array('fields'=>'count(*) as total_items'));
			 $total_items = $t['total_items'];
			
			 $perPage = 2;   
			 $path = $app['url_generator']->generate('dashboard_customer_home');
			 extract(\pagination($total_items,$perPage,$filter_vars,$path));	

			//Set Params
			 $params['paginate'] = $paginate_links; 
			 $params['offset'] = $offset;
			 $params['data'] = $customer->retrieve($filter,array('limit_number'=>$perPage,'limit_offset'=>$offset));
		   
		  return $app['twig']->render('/dashboard/customer_index.html',$params);
	
		})->method('GET|POST')
		  ->bind('dashboard_customer_home');  
		  
		$controllers->match('/customeritem/', function(Request $request) use ($app) {
		     $campaign = $app['campaign'];
			 $customerItem = $app['customeritem'];
			
			//Action
			if($cid = $app['request']->query->get('publish')){
				$customerItem->setStatus($cid,'publish');
			}elseif($cid = $app['request']->query->get('banned')){
				$customerItem->setStatus($cid,'banned');
			}
			
			//Filter
			$filter = array();
			$filter_vars = array();
		
			
			//Pagination Setup
			 list($t) = $customerItem->retrieve($filter,array('fields'=>'count(*) as total_items'));
			 $total_items = $t['total_items'];
			
			 $perPage = 2;   
			 $path = $app['url_generator']->generate('dashboard_customer_home');
			 extract(\pagination($total_items,$perPage,$filter_vars,$path));	

			//Set Params
			 $params['paginate'] = $paginate_links; 
			 $params['offset'] = $offset;
			 $params['data'] = $customerItem->retrieve($filter,array('limit_number'=>$perPage,'limit_offset'=>$offset));
		   
		  return $app['twig']->render('/dashboard/customeritem_index.html',$params);
	
		})->method('GET|POST')
		  ->bind('dashboard_customeritem_home');  

		$controllers->match('/page/addedit/success/{id}', function(Request $request,$id) use ($app) {
		    
			return $app['twig']->render('/dashboard/addedit_success.html',array('addURL' => $app['url_generator']->generate('dashboard_page_addedit'),
																				'editURL' => $app['url_generator']->generate('dashboard_page_addedit')."?edit=$id"));
		
		})->method('GET|POST')
		  ->bind('dashboard_page_addedit_success')->value('id', null);  		  

		$controllers->match('/page/', function(Request $request) use ($app) {
		     $campaign = $app['campaign'];
			 $page = $app['page'];
			
			//Action
			if($cid = $app['request']->query->get('publish')){
				$page->setStatus($cid,'publish');
			}elseif($cid = $app['request']->query->get('pending')){
				$page->setStatus($cid,'pending');
			}
			
			//Filter
			$filter = array();
			$filter_vars = array();
		
			
			//Pagination Setup
			 list($t) = $page->retrieve($filter,array('fields'=>'count(*) as total_items'));
			 $total_items = $t['total_items'];
			
			 $perPage = 2;   
			 $path = $app['url_generator']->generate('dashboard_page_home');
			 extract(\pagination($total_items,$perPage,$filter_vars,$path));	

			//Set Params
			 $params['paginate'] = $paginate_links; 
			 $params['offset'] = $offset;
			 $params['data'] = $page->retrieve($filter,array('limit_number'=>$perPage,'limit_offset'=>$offset));
		   
		  return $app['twig']->render('/dashboard/page_index.html',$params);
	
		})->method('GET|POST')
		  ->bind('dashboard_page_home'); 

		$controllers->match('/page/addedit', function(Request $request) use ($app) {
			$campaign = $app['campaign'];
			$page = $app['page'];
         $action = $app['request']->query->get('edit') ? 'edit' : 'add';
		 
		 $rows = $campaign->retrieve(null,array('fields'=>'slx_campaigns.campaign_id,slx_campaigns.title'));
		 $campaign_choices = array();
		 foreach ($rows as $row) $campaign_choices[$row['campaign_id']] = $row['title'];
		 
		 
		 if($action == 'edit'){
		  $db = $page->getById($app['request']->query->get('edit')); 
		  $data_default['campaign_id'] = $db['campaign_id'];
		  $data_default['page_id'] = $db['page_id'];
		  $data_default['action'] = $action;
		  $data_default['page_title'] = $db['page_title'];
		  $data_default['page_body'] = $db['page_body'];
		  $data_default['page_status'] = $db['page_status'];
		  $data_default['page_publish_date'] = new \DateTime($db['page_publish_date']);
		 }else{
		  $data_default = array('action'=>$action);	
		 }
		 
		 $builder = $app['form.factory']->createBuilder('form',$data_default);
			$form = $builder
					->add('action','hidden')
					->add('page_id','hidden')
					->add('campaign_id','choice',array('choices' => $campaign_choices,'label' => 'Pick Campaign'))
					->add('page_title', 'text', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'Fill the blank'),
						'label'		  => 'Title'
					))
					->add('page_body', 'textarea', array(
						'constraints' => new Assert\NotBlank(),
						'attr'        => array('placeholder' => 'Fill the blank'),
						'label'		  => 'Body Content'
					))
					->add('page_publish_date', 'datetime',array('label' => 'Publish Date'))
					->add('page_status', 'choice',  array(
						'choices'  => array('publish'=>'Publish','pending'=>'Pending')
						))
					->getForm();
				
				
			if ('POST' === $app['request']->getMethod()) {
				$form->bindRequest($app['request']);
				if ($form->isValid()) {
					 $values = $form->getData();
					 $action = $values['action'];
					 unset($values['action']);
					 $values['page_publish_date'] = $values['page_publish_date']->format('Y-m-d H:i:s');
					 if($action == 'edit'){
						$resultID = $page->update($values);
					 }else{
					 unset($values['page_id']);
						$resultID = $page->add($values);
					 }
					  $app['session']->setFlash('success', 'Submission Succeed');
					return $app->redirect($app['url_generator']->generate('dashboard_page_addedit_success',array('id'=>$resultID)));
				} else {
					$form->addError(new FormError('This is a global error'));
					 $app['session']->setFlash('success', 'Submission Failed');
				}
			} 
			 return $app['twig']->render('/dashboard/page_addedit.html',array('form'=>$form->createView()));
		})->method('GET|POST')
		  ->bind('dashboard_page_addedit'); 


		 

		$controllers->match('/page/addedit/success/{id}', function(Request $request,$id) use ($app) {
		    
			return $app['twig']->render('/dashboard/addedit_success.html',array('addURL' => $app['url_generator']->generate('dashboard_page_addedit'),
																				'editURL' => $app['url_generator']->generate('dashboard_page_addedit')."?edit=$id"));
		
		})->method('GET|POST')
		  ->bind('dashboard_page_addedit_success')->value('id', null);  		  
     
		  
        return $controllers;
    }
}