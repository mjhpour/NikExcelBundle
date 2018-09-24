<?php

namespace Nik\ExcelBundle\Steps\Step;

use Doctrine\ORM\EntityManager;
use Nik\ExcelBundle\Exception\MethodNotRegisteredException;
use Nik\ExcelBundle\Steps\StepInterface;

abstract class FilterStep implements StepInterface
{
    /**
     * @var \SplPriorityQueue
     */
    protected $filters;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct($entity = null)
    {
        $this->filters = new \SplPriorityQueue();
        if ($entity instanceof  EntityManager) {
            $this->em = $entity;
        }
    }


    /**
     * {@inheritdoc}
     */
    public function process($name)
    {
        foreach (clone $this->filters as $filter) {
            if ($filter->getName() === $name) {
                return $filter;
            }
        }
        return null;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        if (is_null($this->em)) {
            throw new MethodNotRegisteredException('Entity manager not registered in this class!');
        }
        return $this->em;
    }
}