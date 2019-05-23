<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\MBSite;


interface StyleDataInterface
{
    public function getContent(string $fileName, string $formName): ?string;
}
