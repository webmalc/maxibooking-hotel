<?php

namespace MBH\Bundle\BaseBundle\Lib;


interface TranslatableInterface
{
    public function getTitle();
    public function setTitle($title);
    public function setTranslatableLocale($locale);
}