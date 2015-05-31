<?php

namespace SteveEdson\Account;


class AccountManager {

    public $db;

    function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function findAccount($username, $password) {

        $statement = $this->db->prepare("SELECT * FROM account WHERE username = :username LIMIT 1");

        $result = $statement->execute([
            "username" => $username
        ]);

        if($result && $statement->rowCount() == 1) {

            /**
             * @var Account $account
             */
            $account = $statement->fetchObject('SteveEdson\Account\Account');

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

    public function loadAccount($id) {

        $statement = $this->db->prepare("SELECT * FROM account WHERE id = :id LIMIT 1");

        $result = $statement->execute([
            "id" => $id
        ]);

        if($result && $statement->rowCount() == 1) {

            /**
             * @var Account $account
             */
            $account = $statement->fetchObject('SteveEdson\Account\Account');

            return $account ?: false;

        } else {
            return false;
        }
    }

    /**
     * @param $password
     * @return bool|string
     */
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}