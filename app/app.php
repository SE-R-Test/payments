<?php

use SteveEdson\Account\AccountManager;
use SteveEdson\Invoice\InvoiceManager;
use Stripe\Invoice;
use Stripe\Stripe;

require __DIR__ . "/../vendor/autoload.php";

session_start();

$app = new Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' =>  __DIR__ . '/../template'
));

$config = require __DIR__ . "/config.php";

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
    'account' => $app->container->get('account'),
    'stripe' => array(
        'publishable' => $config['stripe']['test']['publishable']
    )
));

$app->get('/', function() use($app) {

    if($app->container->get('account')) {
        $app->redirect($app->urlFor('invoices'));
    } else {
        $app->redirect($app->urlFor('login'));
    }

})->name('home');

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

    $config = require __DIR__ . "/config.php";

    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    $invoices = $invoice_manager->getInvoicesForAccount($app->container->get('account')->getId());

    $app->render('invoices.twig', [
        'invoices' => $invoices
    ]);

})->name('invoices');

$app->get('/invoices/:id', function($id) use($app) {

    $config = require __DIR__ . "/config.php";

    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    $invoice = $invoice_manager->getUserInvoice($app->container->get('account')->getId(), $id);

    $app->render('view_invoice.twig', [
        'invoice' => $invoice
    ]);

})->name('invoices.view');

$app->post('/invoices/:id/pay', function($id) use($app) {

    $post = $app->request()->post();
    $config = require __DIR__ . "/config.php";

    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    $charge = $invoice_manager->payInvoice(
        $app->container->get('account')->getId(),
        $id,
        $post['stripeToken'],
        $post['stripeTokenType'],
        $post['stripeEmail']
    );

    $app->redirect($app->urlFor('invoices.receipt', [ 'id' => $id ]));
})->name('invoices.pay');

$app->get('/invoices/:id/receipt', function($id) use ($app) {
    $config = require __DIR__ . "/config.php";

    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    $invoice = $invoice_manager->getUserInvoice($app->container->get('account')->getId(), $id);

    $charge = $invoice_manager->getCharge($invoice->getStripeChargeId());

    $app->render('invoice_receipt.twig', array(
        'invoice' => $invoice,
        'charge' => $charge
    ));
})->name('invoices.receipt');

$app->get('/logout', function() use($app) {

    $app->container->remove('account');

    session_destroy();
    unset($_SESSION);

    $app->redirect($app->urlFor('login'));

})->name('logout');