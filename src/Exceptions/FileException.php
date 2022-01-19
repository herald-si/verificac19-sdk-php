<?php 
namespace Herald\GreenPass\Exceptions; 
 
use Throwable; 
 
class FileException extends \Exception 
{ 
 
    public function __construct($message = null, $code = 0, Throwable $previous = null) 
    { 
        if (empty($message)) { 
            $message = "Invalid file"; 
        } 
        parent::__construct($message, $code, $previous); 
    } 
} 
 
?>