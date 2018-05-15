<?php

namespace MBH\Bundle\BaseBundle\Lib;


interface HasSpecialSerializableFieldsInterface
{
    const DATE_TYPE = 'date';
    const DATE_TIME_TYPE = 'datetime';
    const TIME_TYPE =  'time';
    const DOCUMENT_TYPE = 'document';
    const EMBEDDED_TYPE = 'embedded';
    const DOCUMENT_COLLECTION_TYPE = 'document_collection';
    const INT_TYPE = 'integer';
    const STRING_TYPE = 'string';
    //by default is rounded to 2 decimal places
    const FLOAT_TYPE = 'float';

    public static function getSpecialSerializeableFieldsTypes(): array ;
}