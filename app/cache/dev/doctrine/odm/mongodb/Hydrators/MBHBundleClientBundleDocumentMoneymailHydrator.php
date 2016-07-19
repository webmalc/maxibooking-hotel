<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundleClientBundleDocumentMoneymailHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate($document, $data, array $hints = array())
    {
        $hydratedData = array();

        /** @Field(type="string") */
        if (isset($data['moneymailShopIDP'])) {
            $value = $data['moneymailShopIDP'];
            $return = (string) $value;
            $this->class->reflFields['moneymailShopIDP']->setValue($document, $return);
            $hydratedData['moneymailShopIDP'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['moneymailKey'])) {
            $value = $data['moneymailKey'];
            $return = (string) $value;
            $this->class->reflFields['moneymailKey']->setValue($document, $return);
            $hydratedData['moneymailKey'] = $return;
        }
        return $hydratedData;
    }
}