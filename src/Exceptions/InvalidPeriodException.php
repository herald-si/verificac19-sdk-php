<?php

namespace Herald\GreenPass\Exceptions;

use Herald\GreenPass\GreenPass;
use Throwable;

class InvalidPeriodException extends \Exception
{
    private $greenpass;

    /**
     * Time Period not valid.
     *
     * @param string $message
     *                        The exception message
     * @param int    $code
     *                        0 = Generic
     *                        1 = iat
     *                        2 = exp
     */
    public function __construct($message = null, $code = 0, Throwable $previous = null, GreenPass $gp = null)
    {
        $this->greenpass = $gp;
        if (empty($message)) {
            $message = 'Invalid time period';
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get greenpass data.
     *
     * @return GreenPass
     */
    public function getGreenPass()
    {
        return $this->greenpass;
    }
}