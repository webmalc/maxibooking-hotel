<?php


namespace MBH\Bundle\PriceBundle\Document;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\DocumentTraits\FindAllRawTrait;

class PromotionRepository extends DocumentRepository
{
    use FindAllRawTrait;
}