<?php

namespace SteveEdson\Account;


class Account {

    protected $id;
    protected $username;
    protected $password;

    function __construct() {

    }

    function getId() {
        return $this->id;
    }

    function getUsername() {
        return $this->username;
    }

    function getPasswordHash() {
        return $this->password;
    }
}