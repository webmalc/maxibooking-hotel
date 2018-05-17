<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use MBH\Bundle\BaseBundle\Service\MBHSerializer;

interface EmbedNormalizableInterface
{
    public function normalize($value, MBHSerializer $serializer);
    public function denormalize($value, MBHSerializer $serializer);
}