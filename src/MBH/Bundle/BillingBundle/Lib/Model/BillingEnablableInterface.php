<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


interface BillingEnablableInterface
{
    public function getIs_enabled();
    public function setIs_enabled($is_enabled);
}