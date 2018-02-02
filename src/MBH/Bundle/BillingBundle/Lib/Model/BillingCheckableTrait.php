<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

trait BillingCheckableTrait
{
    private $is_checked;

    /**
     * @return bool
     */
    public function getIs_checked()
    {
        return $this->is_checked;
    }

    /**
     * @param bool $is_checked
     * @return static
     */
    public function setIs_checked($is_checked)
    {
        $this->is_checked = $is_checked;

        return $this;
    }
}