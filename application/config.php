<?php
// Databases
$app['db.config.driver']    = 'pdo_mysql';
$app['db.config.dbname']    = 'silex';
$app['db.config.host']      = 'localhost';
$app['db.config.user']      = 'root';
$app['db.config.password']  = '';

// Debug
$app['debug'] = true;

//Facebook
$app['facebook.app_id'] = '283749234';
$app['facebook.secret'] = 'asdfh02fh029h3092h30f92hf';

// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';