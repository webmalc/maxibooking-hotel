<?php

namespace MBH\Bundle\PackageBundle\Lib;


interface DocumentRelationOfMortalInterface
{
    public function getSeries();

    public function getNumber();

    public function getIssued();

    public function getExpiry();

    public function getType();

    public function getAuthorityOrganText();
}
