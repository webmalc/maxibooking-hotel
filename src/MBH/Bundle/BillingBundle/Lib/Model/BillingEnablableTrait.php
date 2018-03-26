<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


trait BillingEnablableTrait
{
    private $is_enabled;

    /**
     * @return bool
     */
    public function getIs_enabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     * @return static
     */
    public function setIs_enabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;

        return $this;
    }
}