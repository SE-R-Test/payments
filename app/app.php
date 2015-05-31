<?php

use SteveEdson\Account\AccountManager;

require __DIR__ . "/../vendor/autoload.php";

session_start();

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

if(isset($_SESSION['account_id']) && !empty($_SESSION['account_id'])) {

    $app->container->singleton('account', function ($container) {

        $accountManager = new AccountManager($container['db']);
        $account = $accountManager->loadAccount($_SESSION['account_id']);

        return $account;
    });
}

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => __DIR__ . '/cache',
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$view->appendData(array(
    'account' => $app->container->get('account')
));

$app->get('/', function() use($app) {

    if($app->container->get('account')) {
        $app->redirect($app->urlFor('invoices'));
    } else {
        $app->redirect($app->urlFor('login'));
    }
});

//$app->get('/register', function() use($app) {
//    $app->render('register.twig');
//})->name('register');
//
//$app->post('/register', function() use($app) {
//    print_r($app->request());
//})->name('register.post');

$app->get('/login', function() use($app) {
    $app->render('login.twig');
})->name('login');

$app->post('/login', function() use($app) {

    $request = $app->request();

    $username = $request->post('username');
    $password = $request->post('password');

    $db = $app->container['db'];

    $account_manager = new AccountManager($db);

    $account = $account_manager->findAccount($username, $password);

    if($account) {

        $_SESSION['account_id'] = $account->getId();

        // Store the account session, redirect to dashboard
        $app->redirect($app->urlFor('invoices'));
    } else {
        $app->flash('error', 'Unable to login. Check your Username and password and try again');
        $app->redirect($app->urlFor('login'));
    }

})->name('login.post');

$app->get('/invoices', function() use($app) {
    $app->render('invoices.twig');
})->name('invoices');

$app->get('/invoices/:id', function($id) use($app) {

})->name('invoices.view');

$app->get('/logout', function() use($app) {

    $app->container->remove('account');

    session_destroy();
    unset($_SESSION);

    $app->redirect($app->urlFor('login'));

})->name('logout');