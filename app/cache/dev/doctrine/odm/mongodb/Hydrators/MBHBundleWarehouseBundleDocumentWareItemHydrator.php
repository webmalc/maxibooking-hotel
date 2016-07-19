<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundleWarehouseBundleDocumentWareItemHydrator implements HydratorInterface
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

        /** @ReferenceOne */
        if (isset($data['category'])) {
            $reference = $data['category'];
            if (isset($this->class->fieldMappings['category']['simple']) && $this->class->fieldMappings['category']['simple']) {
                $className = $this->class->fieldMappings['category']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['category'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['category']->setValue($document, $return);
            $hydratedData['category'] = $return;
        }

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

        /** @Field(type="float") */
        if (isset($data['price'])) {
            $value = $data['price'];
            $return = (float) $value;
            $this->class->reflFields['price']->setValue($document, $return);
            $hydratedData['price'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['unit'])) {
            $value = $data['unit'];
            $return = (string) $value;
            $this->class->reflFields['unit']->setValue($document, $return);
            $hydratedData['unit'] = $return;
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