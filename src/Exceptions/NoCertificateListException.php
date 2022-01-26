<?php

namespace Herald\GreenPass\Exceptions;

use Throwable;

/**
 *
 * @url https://github.com/ehn-dcc-development/ehn-dcc-valuesets/blob/main/disease-agent-targeted.json
 */
class NoCertificateListException extends \Exception
{
    public function __construct($message = null, $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "Invalid certificates list: " . $message;
        }
        parent::__construct($message, $code, $previous);
    }
}
