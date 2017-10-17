<?php

namespace Pseudocody\AuthorizeNet;

use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNetApi
{
    protected $loginId;
    protected $transactionKey;

    public function __construct(string $loginId, string $transactionKey)
    {
        $this->loginId = $loginId;
        $this->transactionKey = $transactionKey;
    }

    public function voidTransaction($transactionId, $testMode = null)
    {
        /* Create a merchantAuthenticationType object with authentication details retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->options($merchantAuthentication);

        // Set the transaction's refId
        $refId = 'ref' . time();

        //create a transaction
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType('voidTransaction');
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $response = $this->handleExecution($testMode, $request);

        return $this->handleResponse($response);
    }

    public function authorizeCreditCard(array $order, bool $testMode = null)
    {
        /* Create a merchantAuthenticationType object with authentication details retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->options($merchantAuthentication);

        // Set the transaction's refId
        $refId = 'ref' . time();
        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($order['credit_card']['card_number']);
        $creditCard->setExpirationDate($order['credit_card']['exp_date']);
        $creditCard->setCardCode($order['credit_card']['cvv']);
        // Add the payment data to a paymentType object
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);
        // Create order information
        $orderType = new AnetAPI\OrderType();
        $orderType->setInvoiceNumber($order['invoice_number'] ?? '');
        $orderType->setDescription($order['description'] ?? '');
        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($order['customer']['first_name']);
        $customerAddress->setLastName($order['customer']['last_name']);
        $customerAddress->setZip($order['customer']['zip']);
        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType('authOnlyTransaction');
        $transactionRequestType->setAmount($order['amount']);
        $transactionRequestType->setOrder($orderType);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $response = $this->handleExecution($testMode, $request);

        return $this->handleResponse($response);
    }

    public function capturePreviouslyAuthorizedCreditCard($transactionId, int $amount = null, bool $testMode = null)
    {
        /* Create a merchantAuthenticationType object with authentication details retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->options($merchantAuthentication);

        // Now capture the previously authorized  amount
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType('priorAuthCaptureTransaction');
        if ($amount !== null) {
            $transactionRequestType->setAmount($amount);
        }
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $response = $this->handleExecution($testMode, $request);

        return $this->handleResponse($response);
    }

    protected function options($merchantAuthentication)
    {
        $merchantAuthentication->setName($this->loginId);
        $merchantAuthentication->setTransactionKey($this->transactionKey);
    }

    private function getErrorDetails($transactionResponse): array
    {
        return [
            'error_code' => $transactionResponse->getErrors()[0]->getErrorCode(),
            'error_message' => $transactionResponse->getErrors()[0]->getErrorText()
        ];
    }

    /**
     * @param $testMode
     * @param $request
     *
     * @return AnetAPI\AnetApiResponseType
     */
    private function handleExecution($testMode, $request)
    {
        $controller = new AnetController\CreateTransactionController($request);
        if ($testMode) {
            $response = $controller->executeWithApiResponse(ANetEnvironment::SANDBOX);
        } else {
            $response = $controller->executeWithApiResponse(ANetEnvironment::PRODUCTION);
        }
        return $response;
    }

    private function handleResponse($response)
    {
        if ($response != null) {
            // Check to see if the API request was successfully received and acted upon
            $transactionResponse = $response->getTransactionResponse();

            if ($response->getMessages()->getResultCode() === 'Ok') {
                // Since the API request was successful, look for a transaction response
                // and parse it to display the results of authorizing the card

                if ($transactionResponse == null || $transactionResponse->getMessages() == null) {
                    if ($transactionResponse->getErrors() != null) {
                        return $this->getErrorDetails($transactionResponse);
                    }

                    return 'Transaction Failed';
                }
                // Or, print errors if the API request wasn't successful
            } else {
                if ($transactionResponse != null && $transactionResponse->getErrors() != null) {
                    return $this->getErrorDetails($transactionResponse);
                } else {
                    return [
                        'error_code' => $response->getMessages()->getMessage()[0]->getCode(),
                        'error_message' => $response->getMessages()->getMessage()[0]->getText()
                    ];
                }
            }
        } else {
            return 'No response returned';
        }

        return $response;
    }
}