<?php

namespace Nik\ExcelBundle\Event;

use Nik\ExcelBundle\Mapping\PropertyMapping;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Base class for excel events.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class Event extends BaseEvent
{
    protected $object;
    protected $mapping;

    public function __construct($object, PropertyMapping $mapping)
    {
        $this->object = $object;
        $this->mapping = $mapping;
    }

    /**
     * Accessor to the object being manipulated.
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
