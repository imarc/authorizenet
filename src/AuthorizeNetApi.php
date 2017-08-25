<?php

namespace Pseudocody\AuthorizeNet;

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

    public function authorizeCreditCard(array $order)
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
        $customerAddress->setFirstName($order['customer']['first_name'] ?? '');
        $customerAddress->setLastName($order['customer']['last_name'] ?? '');
        $customerAddress->setCompany($order['customer']['company_name'] ?? '');
        $customerAddress->setAddress($order['customer']['address'] ?? '');
        $customerAddress->setCity($order['customer']['city'] ?? '');
        $customerAddress->setState($order['customer']['state'] ?? '');
        $customerAddress->setZip($order['customer']['zip']);
        $customerAddress->setCountry($order['customer']['country'] ?? '');
        // Create a TransactionRequestType object and add the previous objects to it
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType('authOnlyTransaction');
        $transactionRequestType->setAmount($order['amount']);
        $transactionRequestType->setOrder($orderType);
        $transactionRequestType->setPayment($paymentOne);
        $transactionRequestType->setBillTo($customerAddress);
        // Set the customer's identifying information
        if (isset($order['customer']['data_type'])) {
            $customerData = new AnetAPI\CustomerDataType();
            $customerData->setType($order['customer']['data_type']['type'] ?? '');
            $customerData->setId($order['customer']['data_type']['id'] ?? '');
            $customerData->setEmail($order['customer']['data_type']['email'] ?? '');

            $transactionRequestType->setCustomer($customerData);
        }
        // Add values for transaction settings
        if (isset($order['setting_types'])) {
            foreach ((array)$order['setting_types'] as $settingType) {
                $duplicateWindowSetting = new AnetAPI\SettingType();
                $duplicateWindowSetting->setSettingName($settingType['name']);
                $duplicateWindowSetting->setSettingValue($settingType['value']);

                $transactionRequestType->addToTransactionSettings($duplicateWindowSetting);
            }
        }
        if (isset($order['field_types'])) {
            // Add some merchant defined fields. These fields won't be stored with the transaction,
            // but will be echoed back in the response.
            foreach ((array)$order['field_types'] as $fieldType) {
                $merchantDefinedField = new AnetAPI\UserFieldType();
                $merchantDefinedField->setName($fieldType['name']);
                $merchantDefinedField->setValue($fieldType['value']);

                $transactionRequestType->addToUserFields($merchantDefinedField);
            }
        }
        // Assemble the complete transaction request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        // Create the controller and get the response
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

        return $this->handleResponse($response);
    }

    public function capturePreviouslyAuthorizedAmount($transactionId)
    {
        /* Create a merchantAuthenticationType object with authentication details retrieved from the constants file */
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $this->options($merchantAuthentication);

        // Now capture the previously authorized  amount
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType('priorAuthCaptureTransaction');
        $transactionRequestType->setRefTransId($transactionId);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);

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