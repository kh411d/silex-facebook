<?php

$app->match('/hello', function() use ($app) {
 $sql = "SELECT * FROM slx_customer_rel";
    $post = $app['db']->fetchAssoc($sql);
	print_r($app['facebook']->getUser());
    print_r($post);
})->method('GET|POST');