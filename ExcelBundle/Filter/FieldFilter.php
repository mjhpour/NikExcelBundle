<?php

namespace Nik\ExcelBundle\Filter;

use Nik\ExcelBundle\Steps\Step\QueryStep;

class FieldFilter implements QueryFilterInterface
{
    private $expr;
    private $value;
    private $isEnabled = true;
    private $fieldName;

    function __construct($enabled = true, $expr = QueryStep::IN)
    {
        $this->expr = $expr;
        $this->setIsEnabled($enabled);
    }

    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getExpr()
    {
        return $this->expr;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValues()
    {
        return $this->value;
    }

    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    protected function setIsEnabled($switch)
    {
        $this->isEnabled = $switch;
    }
}