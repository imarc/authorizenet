<?php

namespace Pseudocody\AuthorizeNet;

use Pseudocody\AuthorizeNet\AuthorizeNetApi;
use Pseudocody\AuthorizeNet\Exception\AuthorizeNetException;

class AuthorizeNet
{
    protected $api;

    public function __construct($loginId, $transactionKey, $api = null)
    {
        if (!is_string($loginId)) {
            throw new AuthorizeNetException("Authorize.net Login ID is required - use the 'AUTHORIZE_NET_LOGIN_IN' .env value");
        }
        if (!is_string($transactionKey)) {
            throw new AuthorizeNetException("Authorize.net Transaction Key is required - use the 'AUTHORIZE_NET_TRANSACTION_KEY' .env value");
        }
        if (null === $api) {
            $api = new AuthorizeNetApi($loginId, $transactionKey);
        }
        $this->api = $api;
    }

    public function authorizeCreditCard(array $order, $testMode = null)
    {
        $result = $this->api->authorizeCreditCard($order, $testMode);
        return $result;
    }

    public function capturePreviouslyAuthorizedCreditCard($transactionId, $amount = null, $testMode = null)
    {
        $result = $this->api->capturePreviouslyAuthorizedCreditCard($transactionId, $amount, $testMode);
        return $result;
    }

    public function voidTransaction($transactionId, $testMode = null)
    {
        $result = $this->api->voidTransaction($transactionId, $testMode);
        return $result;
    }
}
