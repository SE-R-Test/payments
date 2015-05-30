<?php

use Slim\Slim;

require __DIR__ . "/../app/app.php";

// Get default slim instance
$app = Slim::getInstance();

// Run the app
$app->run();