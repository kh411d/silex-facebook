<?php
require_once __DIR__.'/vendor/autoload.php';


$includePath = __DIR__.'/src/Acme/lib/PEAR'. PATH_SEPARATOR . __DIR__.'/src/Acme/lib';
set_include_path($includePath); 

use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;


use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

$app = new Silex\Application();
$app['debug'] = true;

	function pagination($total_items,$perPage,array $extraVars = array(),$path = '',$urlVar = 'pageID')
	{
		//Pagination Setup
		require_once "Pager/Sliding.php";
		$pager = new \pager_sliding(array('totalItems'=>$total_items,
										 'perPage'=>$perPage,
										 'urlVar'=>$urlVar,
										 'extraVars'=>$extraVars,
										 'path'=>$path));
		list($offset,) = $pager->getOffsetByPageId();	
		--$offset; //Need to Decrement
		$links = $pager->getLinks(@$_REQUEST[$urlVar]);	
		return array('offset'=>$offset,'paginate_links'=>$links['all'],'paginate_data'=>$links);
	}



require __DIR__ . '/application/config.php';

$app->register(new HttpCacheServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

$app->register(new TranslationServiceProvider(), array(
      'translator.messages' => array(),
	  'translator.domains' => array()
)) ;

 $app->register(new TwigServiceProvider(), array(
    'twig.options'          => array('cache' => false, 'strict_variables' => true),
  'twig.path'             => array(__DIR__ . '/views')
)); 

$app->register(new DoctrineServiceProvider(), array(
    'db.options'    => array(
        'driver'    => $app['db.config.driver'],
        'dbname'    => $app['db.config.dbname'],
        'host'      => $app['db.config.host'],
        'user'      => $app['db.config.user'],
        'password'  => $app['db.config.password'],
    )
));

/* Register ThirdParty Provider */
use Acme\Provider\Service\FacebookServiceProvider;
$app->register(new FacebookServiceProvider());

/* Register Model */
use Acme\Provider\Service\ModelServiceProvider;
$app->register(new ModelServiceProvider(), array('model.models' => array(
    'customer'      => 'Acme\\Model\\Customer',
	'customeritem'      => 'Acme\\Model\\CustomerItem',
	'campaign'      => 'Acme\\Model\\Campaign',
	'page'			=> 'Acme\\Model\\Page'	
)));

/* Register Model */
use Acme\Provider\Service\HelperServiceProvider;
$app->register(new HelperServiceProvider(), array('helper.helpers' => array(
    'facebook'      => 'Acme\\Helper\\Facebook'
)));

//use Acme\Provider\Service\EpiTemplateServiceProvider;
//$app->register(new EpiTemplateServiceProvider());

/* Register Controller */
$app->mount('/home', new Acme\Provider\Controller\HomeControllerProvider());
$app->mount('/dashboard', new Acme\Provider\Controller\DashboardControllerProvider());

$app->before(function() use ($app) {
    $app['session']->start();
});

//require __DIR__ . '/application/controller.php';

if ($app['debug']) {
    return $app->run();
}

$app['http_cache']->run();