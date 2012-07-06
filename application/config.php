<?php
date_default_timezone_set('Asia/Jakarta');
// Databases
$app['db.config.driver']    = 'pdo_mysql';
$app['db.config.dbname']    = 'silex';
$app['db.config.host']      = 'localhost';
$app['db.config.user']      = 'root';
$app['db.config.password']  = '';

// Debug
$app['debug'] = true;

//Facebook
$app['facebook.app_id'] = '319158791508303';
$app['facebook.secret'] = '1babbcac1b0097cf4cfba191e969b91d';
$app['facebook.permissions'] = 'publish_stream,email';

//EpiTemplate
$app['epi.template.path'] = __DIR__ . '/../views';

// Cache
$app['cache.path'] = __DIR__ . '/../cache';

// Http cache
$app['http_cache.cache_dir'] = $app['cache.path'] . '/http';