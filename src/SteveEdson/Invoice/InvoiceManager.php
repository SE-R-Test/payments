<?php

namespace SteveEdson\Invoice;


class InvoiceManager {

    public $db;

    function __construct(\PDO $db) {
        $this->db = $db;
    }

    function getUnpaidInvoicesForAccount($account_id) {
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
}