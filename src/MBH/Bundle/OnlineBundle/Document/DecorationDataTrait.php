<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Document;


trait DecorationDataTrait
{
    /**
     * @return array
     */
    public static function getThemes(): array
    {
        return self::THEMES;
    }

    public static function getCssLibrariesList(): array
    {
        return self::CSS_LIBRARIES;
    }
}