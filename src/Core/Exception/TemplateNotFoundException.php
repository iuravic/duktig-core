<?php
namespace Duktig\Core\Exception;

class TemplateNotFoundException extends \Exception
{
    /**
     * @param string     $message [optional]
	 * @param int        $code [optional]
	 * @param \Exception $previous [optional]
     */
    public function __construct(string $message = null, int $code = null,
        \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}