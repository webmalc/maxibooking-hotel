<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;


class DefaultDataHandler extends ReportDataHandler
{
    private $valuesByOptions;

    public function setInitData($valuesByOptions)
    {
        $this->valuesByOptions = $valuesByOptions;

        return $this;
    }

    protected function initializeAndReturn($option)
    {
        return $this->valuesByOptions[$option];
    }
}