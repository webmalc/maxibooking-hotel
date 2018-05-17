<?php

namespace MBH\Bundle\BaseBundle\Lib;


interface HasSpecialSerializableFieldsInterface
{
    /**
     * Return class fields types data. Used for classes that don't have doctrine annotations or have some special settings
     *
     * @return array
     */
    public static function getSpecialNormalizationFieldsTypes(): array;
}