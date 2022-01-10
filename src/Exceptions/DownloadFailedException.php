<?php
namespace Herald\GreenPass\Exceptions;

use Throwable;

class DownloadFailedException extends \Exception
{

    const NO_WEBSITE_RESPONSE = "No response was returned from website ";

    const NO_DATA_RESPONSE = "No data was returned from url ";

    public function __construct($message = null, $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = "Failed to download required resource";
        }
        parent::__construct($message, $code, $previous);
    }
}