<?php

require __DIR__ . "/../vendor/autoload.php";

$app = new Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' =>  __DIR__ . '/../template'
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => __DIR__ . '/cache',
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app) {
   $app->render('index.twig');
});

$app->get('/login', function() use($app) {
    $app->render('login.twig');
})->name('login');

$app->get('/invoices', function() use($app) {
    $app->render('invoices.twig');
})->name('invoices');

$app->post('/login', function() use($app) {
    print_r($app->request());
})->name('login.post');