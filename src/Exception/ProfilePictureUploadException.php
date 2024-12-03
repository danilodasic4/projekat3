<?php
namespace App\Exception;

use Exception;

class ProfilePictureUploadException extends Exception
{
    private string $fileName;
    private int $errorCode;

    public function __construct(string $fileName, int $errorCode, string $message = "Error uploading profile picture", int $code = 0, Exception $previous = null)
    {
        $this->fileName = $fileName;
        $this->errorCode = $errorCode;

        $customMessage = $message . " (File: {$fileName}, Error Code: {$errorCode})";

        parent::__construct($customMessage, $code, $previous);
    }

 
    public function getFileName(): string
    {
        return $this->fileName;
    }

 
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getAdditionalErrorInfo(): string
    {
        return "Error occurred while uploading the profile picture '{$this->fileName}' with error code {$this->errorCode}.";
    }
}
