<?php

namespace SteveEdson\Invoice;


use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;

class InvoiceManager {

    public $db;
    public $stripe_api_key;

    function __construct(\PDO $db, $stripe_api_key) {
        $this->db = $db;
        $this->stripe_api_key = $stripe_api_key;

        Stripe::setApiKey($this->stripe_api_key);
    }

    public function getUnpaidInvoicesForAccount($account_id) {
        $statement = $this->db->prepare("SELECT *
                                         FROM invoice
                                         WHERE account_id = :account_id
                                          AND status = 'UNPAID'");

        $result = $statement->execute([
            "account_id" => $account_id
        ]);

        if($result) {
            $invoices = $statement->fetchAll(\PDO::FETCH_CLASS, 'SteveEdson\Invoice\Invoice');
        }

        return $invoices;
    }

    public function getInvoicesForAccount($account_id) {
        $statement = $this->db->prepare("SELECT *
                                         FROM invoice
                                         WHERE account_id = :account_id");

        $result = $statement->execute([
            "account_id" => $account_id
        ]);

        if($result) {
            $invoices = $statement->fetchAll(\PDO::FETCH_CLASS, 'SteveEdson\Invoice\Invoice');
        }

        return $invoices;
    }


    /**
     * @param $account_id
     * @param $invoice_id
     * @return Invoice|false
     */
    public function getUserInvoice($account_id, $invoice_id) {
        $statement = $this->db->prepare("SELECT *
                                         FROM invoice
                                         WHERE account_id = :account_id
                                          AND id = :invoice_id
                                         LIMIT 1");

        $result = $statement->execute([
            "account_id" => $account_id,
            "invoice_id" => $invoice_id
        ]);

        if($result) {

            if($statement->rowCount() == 1) {
                return $statement->fetchObject('SteveEdson\Invoice\Invoice');
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    public function getCharge($charge_id) {
        return Charge::retrieve($charge_id);
    }

    /**
     * @param $account_id
     * @param $invoice_id
     * @param $stripe_token
     * @param $token_type
     * @param $token_email
     * @return Charge
     */
    public function payInvoice($account_id, $invoice_id, $stripe_token, $token_type, $token_email) {

        $invoice = $this->getUserInvoice($account_id, $invoice_id);

        $customer = Customer::create(array(
            'email' => $token_email,
            'card'  => $stripe_token
        ));

        $charge = Charge::create(array(
            'customer' => $customer->id,
            'amount' => $invoice->getAmount(),
            'currency' => 'gbp'
        ));

        if($charge) {
            $statement = $this->db->prepare('UPDATE invoice
                                             SET status = "PAID",
                                                 payment_date = now(),
                                                 stripe_charge = :charge
                                             WHERE id = :id');

            $statement->execute(array(
                'id' => $invoice_id,
                'charge' => $charge->id
            ));
        }

        return $charge;
    }
}