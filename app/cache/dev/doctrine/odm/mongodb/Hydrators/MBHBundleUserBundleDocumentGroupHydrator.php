<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundleUserBundleDocumentGroupHydrator implements HydratorInterface
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
        if (isset($data['name'])) {
            $value = $data['name'];
            $return = (string) $value;
            $this->class->reflFields['name']->setValue($document, $return);
            $hydratedData['name'] = $return;
        }

        /** @Field(type="collection") */
        if (isset($data['roles'])) {
            $value = $data['roles'];
            $return = $value;
            $this->class->reflFields['roles']->setValue($document, $return);
            $hydratedData['roles'] = $return;
        }

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['code'])) {
            $value = $data['code'];
            $return = (string) $value;
            $this->class->reflFields['code']->setValue($document, $return);
            $hydratedData['code'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['createdAt'])) {
            $value = $data['createdAt'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['createdAt']->setValue($document, clone $return);
            $hydratedData['createdAt'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['updatedAt'])) {
            $value = $data['updatedAt'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['updatedAt']->setValue($document, clone $return);
            $hydratedData['updatedAt'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['deletedAt'])) {
            $value = $data['deletedAt'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['deletedAt']->setValue($document, clone $return);
            $hydratedData['deletedAt'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['createdBy'])) {
            $value = $data['createdBy'];
            $return = (string) $value;
            $this->class->reflFields['createdBy']->setValue($document, $return);
            $hydratedData['createdBy'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['updatedBy'])) {
            $value = $data['updatedBy'];
            $return = (string) $value;
            $this->class->reflFields['updatedBy']->setValue($document, $return);
            $hydratedData['updatedBy'] = $return;
        }
        return $hydratedData;
    }
}