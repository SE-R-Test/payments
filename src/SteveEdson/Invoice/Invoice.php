<?php

namespace SteveEdson\Invoice;


class Invoice {
    protected $id;
    protected $account_id;
    protected $title;
    protected $amount;
    protected $stripe_id;
    protected $description;
    protected $issue_date;
    protected $status;
    protected $stripe_charge;
    protected $payment_date;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAccountId() {
        return $this->account_id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getStripeId() {
        return $this->stripe_id;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getIssueDate() {
        return $this->issue_date;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getAmount() {
        return $this->amount;
    }

    public function getStripeChargeId() {
        return $this->stripe_charge;
    }

    /**
     * @return mixed
     */
    public function getPaymentDate() {
        return $this->payment_date;
    }
}