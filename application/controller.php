<?php

$app->match('/hello', function() use ($app) {
 
	print_r($app['facebook']);
	print_r($app['customer']->get_all());
})->method('GET|POST');