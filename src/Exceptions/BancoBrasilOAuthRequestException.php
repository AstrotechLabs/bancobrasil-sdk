<?php

namespace Astrotech\BancoBrasilPix\Exceptions;

use Exception;

final class BancoBrasilOAuthRequestException extends Exception
{
    public function __construct()
    {
        $message = 'There was error on make oAuth Request';
        parent::__construct($message);
    }
}
