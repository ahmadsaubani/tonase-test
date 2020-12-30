<?php

namespace App\Exceptions;

class CustomMessagesException extends \Exception
{
    private $realException;

    private $statusCode = 200;

    private $forceReport = false;

    /**
     * CustomMessagesException constructor.
     * @param array $messages
     * @param int $statusCode
     * @param \Exception|null $realException
     * @param $forceReport
     * @param \Exception|null $previous
     * @param int $code
     */
    public function __construct($messages = [], $statusCode = 200, \Exception $realException = null, $forceReport = false, \Exception $previous = null, $code = 0)
    {
        $this->realException = $realException;

        $this->forceReport = $forceReport;

        if (is_array($messages)) {
            $messages = json_encode($messages);
        }

        $this->statusCode = $statusCode;
        parent::__construct($messages, $statusCode, $previous);
    }

    public function getMessages()
    {
        return json_decode($this->message);
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getRealException()
    {
        return $this->realException;
    }

    /**
     * @return bool
     */
    public function isForceReport(): bool
    {
        return $this->forceReport;
    }
}
