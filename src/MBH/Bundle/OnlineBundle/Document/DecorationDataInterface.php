<?php
/**
 * Created by PhpStorm.
 * Date: 31.05.18
 */

namespace MBH\Bundle\OnlineBundle\Document;


interface DecorationDataInterface
{
    const THEMES = [
        "cerulean"  => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css",
        "cosmo"     => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cosmo/bootstrap.min.css",
        "cyborg"    => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cyborg/bootstrap.min.css",
        "darkly"    => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/darkly/bootstrap.min.css",
        "flatly"    => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css",
        "journal"   => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/journal/bootstrap.min.css",
        "lumen"     => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/lumen/bootstrap.min.css",
        "paper"     => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/paper/bootstrap.min.css",
        "readable"  => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/readable/bootstrap.min.css",
        "sandstone" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css",
        "simplex"   => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/simplex/bootstrap.min.css",
        "slate"     => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/slate/bootstrap.min.css",
        "spacelab"  => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/spacelab/bootstrap.min.css",
        "superhero" => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/superhero/bootstrap.min.css",
        "united"    => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/united/bootstrap.min.css",
        "yeti"      => "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/yeti/bootstrap.min.css",
        "bootstrap" => "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css",
    ];

    const CSS_LIBRARIES = [
        "font_awesome" => "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css",
    ];

    /**
     * @return array
     */
    public static function getThemes(): array;

    /**
     * @return array
     */
    public static function getCssLibrariesList(): array;
}