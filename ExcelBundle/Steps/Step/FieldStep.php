<?php

namespace Nik\ExcelBundle\Steps\Step;

/**
 * The filter step determines whether the input data should be processed further.
 * If any of the callables in the step returns false field was skipped.
 *
 * Class FieldStep
 * @package Nik\ExcelBundle\Steps\Step
 */
class FieldStep extends FilterStep
{
    /**
     * @param callable $filter
     * @param null $priority
     * @return $this
     */
    public function add(callable $filter, $priority = null)
    {
        $this->filters->insert($filter, $priority);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        foreach (clone $this->filters as $filter) {
            if (false === call_user_func($filter, $item)) {
                return false;
            }
        }
        return true;
    }

}