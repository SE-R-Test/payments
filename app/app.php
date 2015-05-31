<?php

require __DIR__ . "/../vendor/autoload.php";

$app = new Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' =>  __DIR__ . '/../template'
));

$app->container->singleton('db', function($container) {
    $config = require __DIR__ . "/config.php";

    $dsn = 'mysql:host=' . $config['database']['hostname'] . ';dbname=' . $config['database']['dbname'];

    if(!empty($config['database']['port'])) {
        $dsn .= ";port=" . $config['database']['port'];
    }

    $db = new PDO($dsn, $config['database']['username'], $config['database']['password']);

    return $db;
});

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

$app->get('/register', function() use($app) {
    $app->render('register.twig');
})->name('register');

$app->post('/register', function() use($app) {
    print_r($app->request());
})->name('register.post');

$app->get('/login', function() use($app) {
    $app->render('login.twig');
})->name('login');

$app->post('/login', function() use($app) {
    print_r($app->request());
})->name('login.post');

$app->get('/invoices', function() use($app) {
    $app->render('invoices.twig');
})->name('invoices');

$app->get('/invoices/:id', function($id) use($app) {

})->name('invoices.view');