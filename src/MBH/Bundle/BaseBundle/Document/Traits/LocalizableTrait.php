<?php

namespace MBH\Bundle\BaseBundle\Document\Traits;

trait LocalizableTrait
{
    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return static
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}