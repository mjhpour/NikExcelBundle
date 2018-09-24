<?php

namespace Nik\ExcelBundle\Exception;

use Doctrine\ORM\ORMException;

/**
 * Exception thrown when a Proxy fails to retrieve an Entity result.
 *
 * @author robo
 * @since 2.0
 */
class NikEntityNotFoundException extends ORMException
{
    /**
     * Constructor.
     */
    public function __construct($message = 'Entity was not found.')
    {
        parent::__construct($message);
    }
}
