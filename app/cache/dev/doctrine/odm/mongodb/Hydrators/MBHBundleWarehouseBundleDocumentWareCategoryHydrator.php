<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundleWarehouseBundleDocumentWareCategoryHydrator implements HydratorInterface
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

        /** @Many */
        $mongoData = isset($data['items']) ? $data['items'] : null;
        $return = new \Doctrine\ODM\MongoDB\PersistentCollection(new \Doctrine\Common\Collections\ArrayCollection(), $this->dm, $this->unitOfWork);
        $return->setHints($hints);
        $return->setOwner($document, $this->class->fieldMappings['items']);
        $return->setInitialized(false);
        if ($mongoData) {
            $return->setMongoData($mongoData);
        }
        $this->class->reflFields['items']->setValue($document, $return);
        $hydratedData['items'] = $return;

        /** @Field(type="string") */
        if (isset($data['fullTitle'])) {
            $value = $data['fullTitle'];
            $return = (string) $value;
            $this->class->reflFields['fullTitle']->setValue($document, $return);
            $hydratedData['fullTitle'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['title'])) {
            $value = $data['title'];
            $return = (string) $value;
            $this->class->reflFields['title']->setValue($document, $return);
            $hydratedData['title'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['system'])) {
            $value = $data['system'];
            $return = (bool) $value;
            $this->class->reflFields['system']->setValue($document, $return);
            $hydratedData['system'] = $return;
        }

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['isEnabled'])) {
            $value = $data['isEnabled'];
            $return = (bool) $value;
            $this->class->reflFields['isEnabled']->setValue($document, $return);
            $hydratedData['isEnabled'] = $return;
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