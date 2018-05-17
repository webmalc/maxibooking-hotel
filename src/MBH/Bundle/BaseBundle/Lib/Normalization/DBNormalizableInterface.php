<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Doctrine\ODM\MongoDB\DocumentManager;

interface DBNormalizableInterface
{
    public function normalize($value);
    public function denormalize($value, DocumentManager $dm);
}