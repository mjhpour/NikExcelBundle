<?php

namespace Nik\ExcelBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Method not registered exception.
 *
 * Class MethodNotRegisteredException
 * @package Nik\ExcelBundle\Exception
 */
class MethodNotRegisteredException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(404, $message, $previous, array(), $code);
    }
}