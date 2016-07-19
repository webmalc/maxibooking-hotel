<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundleOnlineBundleDocumentInviteHydrator implements HydratorInterface
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

        /** @Field(type="date") */
        if (isset($data['arrival'])) {
            $value = $data['arrival'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['arrival']->setValue($document, clone $return);
            $hydratedData['arrival'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['departure'])) {
            $value = $data['departure'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['departure']->setValue($document, clone $return);
            $hydratedData['departure'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['type'])) {
            $value = $data['type'];
            $return = (string) $value;
            $this->class->reflFields['type']->setValue($document, $return);
            $hydratedData['type'] = $return;
        }

        /** @Many */
        $mongoData = isset($data['guests']) ? $data['guests'] : null;
        $return = new \Doctrine\ODM\MongoDB\PersistentCollection(new \Doctrine\Common\Collections\ArrayCollection(), $this->dm, $this->unitOfWork);
        $return->setHints($hints);
        $return->setOwner($document, $this->class->fieldMappings['guests']);
        $return->setInitialized(false);
        if ($mongoData) {
            $return->setMongoData($mongoData);
        }
        $this->class->reflFields['guests']->setValue($document, $return);
        $hydratedData['guests'] = $return;

        /** @Many */
        $mongoData = isset($data['tripRoutes']) ? $data['tripRoutes'] : null;
        $return = new \Doctrine\ODM\MongoDB\PersistentCollection(new \Doctrine\Common\Collections\ArrayCollection(), $this->dm, $this->unitOfWork);
        $return->setHints($hints);
        $return->setOwner($document, $this->class->fieldMappings['tripRoutes']);
        $return->setInitialized(false);
        if ($mongoData) {
            $return->setMongoData($mongoData);
        }
        $this->class->reflFields['tripRoutes']->setValue($document, $return);
        $hydratedData['tripRoutes'] = $return;

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

        /** @ReferenceOne */
        if (isset($data['hotel'])) {
            $reference = $data['hotel'];
            if (isset($this->class->fieldMappings['hotel']['simple']) && $this->class->fieldMappings['hotel']['simple']) {
                $className = $this->class->fieldMappings['hotel']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['hotel'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['hotel']->setValue($document, $return);
            $hydratedData['hotel'] = $return;
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