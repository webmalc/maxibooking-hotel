<?php

namespace MBH\Bundle\BaseBundle\EventListener;


/**
 * Class TimestampableListener
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TranslatableListener extends \Gedmo\Translatable\TranslatableListener
{
    public function __construct()
    {
        parent::__construct();

        //$this->setDefaultLocale('en_EN');
        //$this->setTranslatableLocale('en_EN');
    }
}