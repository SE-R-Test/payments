<?php

use SteveEdson\Account\AccountManager;
use SteveEdson\Invoice\InvoiceManager;

// Require composer files
require __DIR__ . "/../vendor/autoload.php";

// Start PHP session
session_start();

/**
 * Initialise Slim instance
 *
 * @see http://www.slimframework.com/
 */
$app = new Slim\Slim(array(
    'view' => new \Slim\Views\Twig(), // Add Twig template engine
    'templates.path' =>  __DIR__ . '/../template'
));

// Inject the DB, so that it can be used anywhere
$app->container->singleton('db', function() {
    // Load configuration file
    $config = require __DIR__ . "/config.php";

    // Construct the connection DSN
    $dsn = 'mysql:host=' . $config['database']['hostname'] . ';dbname=' . $config['database']['dbname'];

    // If the port has been set, add it to the DSN
    if(!empty($config['database']['port'])) {
        $dsn .= ";port=" . $config['database']['port'];
    }

    // Connect to the database, using PHP PDO
    return new PDO($dsn, $config['database']['username'], $config['database']['password']);
});

// If the account ID exists in the session
if(isset($_SESSION['account_id']) && !empty($_SESSION['account_id'])) {

    // Add the account to the app, to be used everywhere
    $app->container->singleton('account', function ($container) {

        // Create the account manager, and load the account
        $accountManager = new AccountManager($container['db']);
        $account = $accountManager->loadAccount($_SESSION['account_id']);

        return $account;
    });
}

// Add set twig template options
$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => __DIR__ . '/cache',
);

// Add the twig extension, so that we can use functions such as 'urlFor()' in our templates
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

// Load configuration file
$config = require __DIR__ . "/config.php";
// Add the account data into the templates, and the publishable Stripe key
$view->appendData(array(
    'account' => $app->container->get('account'),
    'stripe' => array(
        'publishable' => $config['stripe']['test']['publishable']
    )
));

/**
 * Index
 * Redirect to invoices, or login screen
 */
$app->get('/', function() use($app) {

    if($app->container->get('account')) {
        $app->redirect($app->urlFor('invoices'));
    } else {
        $app->redirect($app->urlFor('login'));
    }

})->name('home');

/**
 * Login page
 */
$app->get('/login', function() use($app) {
    $app->render('login.twig');
})->name('login');

/**
 * POST Request containing login data
 */
$app->post('/login', function() use($app) {

    // Get the request object
    $request = $app->request();

    // Get the post variables
    $username = $request->post('username');
    $password = $request->post('password');

    // Get the account manager instance
    $account_manager = new AccountManager($app->container['db']);

    /**
     * Find any accounts with this username, and matching passwords
     *
     * @note The password is hashed and is secure
     */
    $account = $account_manager->findAccount($username, $password);

    // If the account has been found
    if($account) {
        // Store the account id in the session
        $_SESSION['account_id'] = $account->getId();

        // Redirect to dashboard
        $app->redirect($app->urlFor('invoices'));
    } else {
        // Set the error message and redirect back to the login page
        $app->flash('error', 'Unable to login. Check your Username and password and try again');
        $app->redirect($app->urlFor('login'));
    }
})->name('login.post');

/**
 * Invoice List
 */
$app->get('/invoices', function() use($app) {

    // Load the config and pass the stripe api key into the invoice manager, along with the db instance
    $config = require __DIR__ . "/config.php";
    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    // Get all invoices for this account
    $invoices = $invoice_manager->getInvoicesForAccount($app->container->get('account')->getId());

    // Render the page, using the available invoices
    $app->render('invoices.twig', [
        'invoices' => $invoices
    ]);
})->name('invoices');

/**
 * View a specified invoice
 */
$app->get('/invoices/:id', function($id) use($app) {

    // Load the config and pass the stripe api key into the invoice manager, along with the db instance
    $config = require __DIR__ . "/config.php";
    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    // Get the invoice, using the account id, so that invoices belonging to other accounts can not be loaded
    $invoice = $invoice_manager->getUserInvoice($app->container->get('account')->getId(), $id);

    // Render the invoice page
    $app->render('view_invoice.twig', [
        'invoice' => $invoice
    ]);
})->name('invoices.view');

/**
 * POST request with payment details
 */
$app->post('/invoices/:id/pay', function($id) use($app) {

    // Get the post info
    $post = $app->request()->post();

    // Load the config and pass the stripe api key into the invoice manager, along with the db instance
    $config = require __DIR__ . "/config.php";
    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    // Pass the data into the payInvoice method
    $charge = $invoice_manager->payInvoice(
        $app->container->get('account')->getId(),
        $id,
        $post['stripeToken'],
        $post['stripeTokenType'],
        $post['stripeEmail']
    );

    if($charge) {
        $app->redirect($app->urlFor('invoices.receipt', ['id' => $id]));
    } else {
        // Set the error message and redirect back to the login page
        $app->flash('error', 'Unable to pay invoice. Please try again.');
        $app->redirect($app->urlFor('invoices.view', [ 'id' => $id ]));
    }
})->name('invoices.pay');

/**
 * View the payment receipt for an invoice
 */
$app->get('/invoices/:id/receipt', function($id) use ($app) {

    // Load the config and pass the stripe api key into the invoice manager, along with the db instance
    $config = require __DIR__ . "/config.php";
    $invoice_manager = new InvoiceManager($app->container->get('db'), $config['stripe']['test']['secret']);

    // Load the invoice
    $invoice = $invoice_manager->getUserInvoice($app->container->get('account')->getId(), $id);

    // Get the payment data from Stripe
    $charge = $invoice_manager->getCharge($invoice->getStripeChargeId());

    // Render the page
    $app->render('invoice_receipt.twig', array(
        'invoice' => $invoice,
        'charge' => $charge
    ));
})->name('invoices.receipt');

/**
 * Logout and redirect
 */
$app->get('/logout', function() use($app) {

    $app->container->remove('account');

    session_destroy();
    unset($_SESSION);

    $app->redirect($app->urlFor('login'));

})->name('logout');