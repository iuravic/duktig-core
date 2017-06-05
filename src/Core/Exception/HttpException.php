<?php
namespace Duktig\Core\Exception;

class HttpException extends \Exception
{
    /**
     * @var int $statusCode
     */
    protected $statusCode;
    
    /**
     * @param int        $statusCode
     * @param string     $message [optional]
	 * @param int        $code [optional]
	 * @param \Exception $previous [optional]
     */
    public function __construct(int $statusCode, string $message = null, 
        int $code = null, \Exception $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * @return int $statusCode
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }
}