<?php

namespace SteveEdson\Account;


class Account {

    protected $username;
    protected $password;

    function __construct() {

    }

    function getUsername() {
        return $this->username;
    }

    function getPasswordHash() {
        return $this->password;
    }
}