<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 16:31
 */

namespace MBH\Bundle\BaseBundle\Lib;

interface Exportable
{
    public static function getExportableFieldsData(): array;
}