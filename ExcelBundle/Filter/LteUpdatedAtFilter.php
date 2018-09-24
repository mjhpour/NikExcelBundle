<?php

namespace Nik\ExcelBundle\Filter;

use Nik\ExcelBundle\Steps\Step\QueryStep;

/**
 * Filter excel result by update at field.
 *
 * Class LteUpdatedAtFilter
 * @package Nik\ExcelBundle\Filter
 */
class LteUpdatedAtFilter implements QueryFilterInterface
{
    private $expr;
    private $value;
    private $isEnabled = true;

    function __construct(\DateTime $dateTime, $enabled = true, $expr = null)
    {
        $this->value = $dateTime;
        $this->expr = is_null($expr) ? QueryStep::LTE : $expr;
        $this->setIsEnabled($enabled);
    }

    public function getValues()
    {
        return $this->value->format("Y-m-d H:i:s");
    }

    public function getExpr()
    {
        return $this->expr;
    }

    public function getFieldName()
    {
        return 'updatedAt';
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