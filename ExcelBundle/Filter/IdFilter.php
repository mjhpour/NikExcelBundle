<?php

namespace Nik\ExcelBundle\Filter;

use Nik\ExcelBundle\Steps\Step\QueryStep;

/**
 * Filter excel result by id.
 *
 * Class IdFilter
 * @package Nik\ExcelBundle\Filter
 */
class IdFilter implements QueryFilterInterface
{
    private $expr;
    private $values;
    private $isEnabled = true;

    function __construct(array $ids, $enabled = true, $expr = null)
    {
        $this->values = $ids;
        $this->expr = is_null($expr) ? QueryStep::IN : $expr;
        $this->setIsEnabled($enabled);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getExpr()
    {
        return $this->expr;
    }

    public function getFieldName()
    {
        return 'id';
    }

    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    public function setIsEnabled($switch)
    {
        $this->isEnabled = $switch;
    }
}