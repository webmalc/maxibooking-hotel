<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


interface BillingCheckableInterface
{
    public function getIs_checked();
    public function setIs_checked($is_checked);
}