# Authorize.net

Basic abstraction with Laravel integration for Authorize.net
 
### Installation

- `composer require pseudocody/authorizenet`
- For Laravel 5 support:
    - Add the service provider to `config/app.php`: `Pseudocody\AuthorizeNet\AuthorizeNetServiceProvider`
    - Register the facade: `'AuthorizeNet' => Pseudocody\AuthorizeNet\AuthorizeNetFacade::class,`
    - Add `.env` value for `AUTHORIZE_NET_LOGIN` (Login ID) and `AUTHORIZE_NET_TRANSACTION_KEY` (Transaction Key)
    - Optionally create the config file: `config/authorizenet.php`
    
### Usage
- Within Laravel 5, instantiate using the API key in the constructor: `$authorizeNet = new Pseudocody\AuthorizeNet\AuthorizeNet($loginId, $transactionKey)`
- `AuthorizeNet::authorizeCreditCard($order)` attempts authorize cards with provided information, returns response
- `AuthorizeNet::capturePreviouslyAuthorizedCreditcard($transactionId, $amount)` attempts to capture previously authorized credit card

