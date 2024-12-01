<?php
namespace App\Exception;

use Exception;

class ProfilePictureUploadException extends Exception
{
    public function __construct(string $message = "Error uploading profile picture", int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
