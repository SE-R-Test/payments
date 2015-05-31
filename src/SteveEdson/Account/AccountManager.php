<?php

namespace SteveEdson\Account;


class AccountManager {

    public $db;

    /**
     * @param \PDO $db Database connection
     */
    function __construct(\PDO $db) {
        $this->db = $db;
    }

    /**
     * Find account with a username, and matching password
     *
     * @param $username Username of the account
     * @param $password Unhashed password
     * @return bool|Account
     */
    public function findAccount($username, $password) {

        // Prepare the SQL statment using named parameters
        $statement = $this->db->prepare("SELECT * FROM account WHERE username = :username LIMIT 1");

        // Execute the statement
        $result = $statement->execute([
            "username" => $username
        ]);

        // If the result was successful, and a record was found
        if($result && $statement->rowCount() == 1) {

            /**
             * @var Account $account
             */
            $account = $statement->fetchObject('SteveEdson\Account\Account');

            // Account has been found, verify that the password matches the hash that is stored
            if(password_verify($password, $account->getPasswordHash())) {
                return $account;
            } else {
                unset($account);
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * Load an account
     *
     * @param $id ID of the account
     * @return bool|Account
     */
    public function loadAccount($id) {

        // Prepare the SQL statement using named parameters
        $statement = $this->db->prepare("SELECT * FROM account WHERE id = :id LIMIT 1");

        // Execute the statement
        $result = $statement->execute([
            "id" => $id
        ]);

        if($result && $statement->rowCount() == 1) {

            /**
             * Fetch the record, mapping it into an Account object
             *
             * @var Account $account
             */
            $account = $statement->fetchObject('SteveEdson\Account\Account');

            return $account ?: false;

        } else {
            return false;
        }
    }
}