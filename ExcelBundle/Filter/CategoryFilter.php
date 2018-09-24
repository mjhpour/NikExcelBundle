<?php

namespace Nik\ExcelBundle\Filter;

use Nik\ExcelBundle\Steps\Step\QueryStep;

class CategoryFilter implements QueryFilterInterface
{
    private $expr;
    private $value;
    private $isEnabled = true;

    function __construct($catId, $enabled = true, $expr = QueryStep::IN)
    {
        $this->value = $catId;
        $this->expr = $expr;
        $this->setIsEnabled($enabled);
    }

    public function getFieldName()
    {
        return 'category';
    }

    public function getExpr()
    {
        return $this->expr;
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