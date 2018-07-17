<?php
/**
 * Created by PhpStorm.
 * Date: 03.05.18
 */

namespace MBH\Bundle\PackageBundle\Lib;


interface DataOfMortalInterface
{
    public function getFullName();

    public function getLastName();

    public function getFirstName();

    public function getBirthday();

    public function getEmail();

    public function getPatronymic();

    public function getShortName();
}