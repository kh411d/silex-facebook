<?php

$app->get('/hello', function() use ($app) {
 $sql = "SELECT * FROM slx_customer_rel";
    $post = $app['db']->fetchAssoc($sql);
	print_r($app['facebook']);
    print_r($post);
});