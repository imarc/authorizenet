# Authorize.net

Basic abstraction with Laravel integration for Authorize.net
 
### Installation

- `composer require pseudocody/authorizenet`
- For Laravel 5 support:
    - Add the service provider to `config/app.php`: `Pseudocody\AuthorizeNet\AuthorizeNetServiceProvider`
    - Register the facade: `'AuthorizeNet' => Pseudocody\AuthorizeNet\AuthorizeNetFacade::class,`
    - Add `.env` value for `AUTHORIZE_NET_LOGIN` (Login ID) and `AUTHORIZE_NET_TRANSACTION_KEY` (Transaction Key)
    - Optionally publish the config file: `php artisan vendor:publish --provider=Pseudocody\AuthorizeNet\AuthorizeNetServiceProvider`
    
### Usage
- Within Laravel 5, use the `AuthorizeNet` facade or instantiate via the container `app(Pseudocody\AuthorizeNet\AuthorizeNet::class)`.
    - Alternatively, instantiate manually using the API key in the constructor: `$authorizeNet = new Pseudocody\AuthorizeNet\AuthorizeNet($loginId, $transactionKey)`

- `AuthorizeNet::authorizeCreditCard($order)` attempts authorize cards with provided information, returns response


    `$order = [
            // Required
            'amount' => 100,
            'credit_card' => [
                'card_number' => '0000000000000000',
                'exp_date' => '1020',
                'cvv' => '555'
            ],
            // Optional
            'invoice_number' => 1,
            'description' => 'Foo Bar',
            'customer' => [
                'first_name' => 'John',
                'last_name' => 'Doe
                'company_name' => 'Big Co Inc.',
                'address' => '5000 Anywhere Street',
                'city' => 'Hometown',
                'state' => 'TX',
                'country' => 'USA',
                'data_type' => [
                    'type' => 'individiual',
                    'id' => 1,
                    'email' => 'john@example.com"
                ]
            ]
    ];`
- `AuthorizeNet::capturePreviouslyAuthorizedAmount($transactionId)`

### Errors

### Examples

```php
```

