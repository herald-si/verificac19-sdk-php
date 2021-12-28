<?php
namespace Herald\GreenPass\Exceptions;

use Throwable;

class DownloadFailedException extends \Exception
{

    public function __construct($message = null, $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "Failed to download required resource";
        }
        parent::__construct($message, $code, $previous);
    }
}