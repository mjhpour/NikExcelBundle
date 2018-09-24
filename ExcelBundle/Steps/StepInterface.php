<?php

namespace Nik\ExcelBundle\Steps;

interface StepInterface
{
    /**
     * Get filter from data stack
     *
     * @param string $name
     */
    public function process($name);
}