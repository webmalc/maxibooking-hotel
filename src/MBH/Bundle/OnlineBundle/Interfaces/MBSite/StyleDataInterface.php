<?php
/**
 * Date: 23.05.19
 */

namespace MBH\Bundle\OnlineBundle\Interfaces\MBSite;


interface StyleDataInterface
{
    public const PREFIX_DIR = '/../src/MBH/Bundle/OnlineBundle/Resources/public/css/mb-site/for-form/';

    public function getStyleData(string $fileName, string $formName): ?string;
}
