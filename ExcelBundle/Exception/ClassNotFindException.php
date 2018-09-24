<?php

namespace Nik\ExcelBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ClassNotFindException
 * @package Nik\ExcelBundle\Exception
 */
class ClassNotFindException extends HttpException
{
    /**
     * Constructor.
     *
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($message = null, \Exception $previous = null, $code = 404)
    {
        parent::__construct(404, $message, $previous, array(), $code);
    }
}