<?php

use Pseudocody\AuthorizeNet\AuthorizeNetApi;

class AuthorizeNetTest extends PHPUnit\Framework\TestCase
{
    const MERCHANT_LOGIN_ID = '5KP3u95bQpv';
    const MERCHANT_TRANSACTION_KEY = '346HZ32z3fP4hTG2';

    protected $api;
    protected $authorizeNet;

    public function setUp()
    {
        $this->api = $this->getMockBuilder(AuthorizeNetApi::class);
        $this->authorizeNet = new Pseudocody\AuthorizeNet\AuthorizeNet(self::MERCHANT_LOGIN_ID, self::MERCHANT_TRANSACTION_KEY);
    }

    public function testTransaction()
    {
        $order = [
            'amount' => 1,
            'credit_card' => [
                'card_number' => '5424000000000015',
                'exp_date' => '0519',
                'cvv' => '669'
            ],
            'customer' => [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'zip' => 75007
            ]
        ];

        $response = $this->authorizeNet->authorizeCreditCard($order);
        $response = $response->getTransactionResponse();

        $this->assertSame((int)$response->getResponseCode(), 1);

        $this->authorizeNet->capturePreviouslyAuthorizedCreditCard($response->getTransId(), 1);
    }

}