<?php
require_once __DIR__.'/vendor/autoload.php';

use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\SessionServiceProvider;
//use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

$app = new Silex\Application();
$app['debug'] = true;

/* Include ThirdParty Provider */
use Acme\Provider\Service\FacebookServiceProvider;
use Acme\Provider\Service\ModelServiceProvider;


require __DIR__ . '/application/config.php';

$app->register(new HttpCacheServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

/* $app->register(new TwigServiceProvider(), array(
    'twig.options'          => array('cache' => false, 'strict_variables' => true),
    'twig.form.templates'   => array('form_div_layout.html.twig', 'common/form_div_layout.html.twig'),
    'twig.path'             => array(__DIR__ . '/../views')
)); */

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
$app->register(new FacebookServiceProvider());
/* Register Model */
$app->register(new ModelServiceProvider(), array('model.models' => array(
    'customer'      => 'Acme\\Model\\Customer'
)));

$app->before(function() use ($app) {
    $app['session']->start();
});

require __DIR__ . '/application/controller.php';

if ($app['debug']) {
    return $app->run();
}

$app['http_cache']->run();