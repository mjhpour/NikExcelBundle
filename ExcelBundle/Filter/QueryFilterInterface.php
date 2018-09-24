<?php

namespace Nik\ExcelBundle\Filter;

// TODO: Add get class name method for check class when we want to generate query.
interface QueryFilterInterface
{
    /**
     * Get name of field that filter do filtering on it.
     *
     * @return mixed
     */
    public function getFieldName();

    /**
     * Get expression that registered to filter for create final query/
     *
     * @return mixed
     */
    public function getExpr();

    /**
     * Get value that added to filter to use in filtering.
     *
     * @return mixed
     */
    public function getValues();

    /**
     * Get filter is enabled or not.
     *
     * @return mixed
     */
    public function getIsEnabled();
}