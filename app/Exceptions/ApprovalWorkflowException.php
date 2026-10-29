<?php

namespace App\Exceptions;

class ApprovalWorkflowException extends \RuntimeException
{
    protected $errorCode;
    protected $statusCode;
    protected $context;

    public function __construct(string $message, string $errorCode = 'APPROVAL_ERROR', int $statusCode = 422, array $context = [])
    {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->context = $context;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}