<?php


namespace MBH\Bundle\PriceBundle\Form\DataTransformer;


use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;

class SpecialsToStringTransformer implements DataTransformerInterface
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function transform($specialArray)
    {
        return $specialArray?implode(',', $specialArray):'';
    }

    public function reverseTransform($specialString)
    {
        $ids = explode(',', $specialString);

        return $this->dm->getRepository('MBHPriceBundle:Special')->findByIds($ids);

    }

}