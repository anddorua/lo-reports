<?php

define('DEBUG', true);

ini_set('display_errors', DEBUG ? 1 : 0);

require_once __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../include/bootstrap.php';
require __DIR__ . '/../include/mount_routes.php';
require __DIR__ . '/../include/error_handling.php';

/* @var $app Silex\Application */
$app->run();
