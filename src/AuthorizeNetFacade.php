<?php

namespace Pseudocody\AuthorizeNet;

use Illuminate\Support\Facades\Facade;

class AuthorizeNetFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'authorizenet';
    }
}