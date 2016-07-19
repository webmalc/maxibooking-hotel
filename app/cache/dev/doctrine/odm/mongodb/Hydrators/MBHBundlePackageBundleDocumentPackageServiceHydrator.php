<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundlePackageBundleDocumentPackageServiceHydrator implements HydratorInterface
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
        if (isset($data['service'])) {
            $reference = $data['service'];
            if (isset($this->class->fieldMappings['service']['simple']) && $this->class->fieldMappings['service']['simple']) {
                $className = $this->class->fieldMappings['service']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['service'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['service']->setValue($document, $return);
            $hydratedData['service'] = $return;
        }

        /** @ReferenceOne */
        if (isset($data['package'])) {
            $reference = $data['package'];
            if (isset($this->class->fieldMappings['package']['simple']) && $this->class->fieldMappings['package']['simple']) {
                $className = $this->class->fieldMappings['package']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['package'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['package']->setValue($document, $return);
            $hydratedData['package'] = $return;
        }

        /** @Field(type="float") */
        if (isset($data['price'])) {
            $value = $data['price'];
            $return = (float) $value;
            $this->class->reflFields['price']->setValue($document, $return);
            $hydratedData['price'] = $return;
        }

        /** @Field(type="float") */
        if (isset($data['total'])) {
            $value = $data['total'];
            $return = (float) $value;
            $this->class->reflFields['total']->setValue($document, $return);
            $hydratedData['total'] = $return;
        }

        /** @Field(type="float") */
        if (isset($data['totalOverwrite'])) {
            $value = $data['totalOverwrite'];
            $return = (float) $value;
            $this->class->reflFields['totalOverwrite']->setValue($document, $return);
            $hydratedData['totalOverwrite'] = $return;
        }

        /** @Field(type="integer") */
        if (isset($data['amount'])) {
            $value = $data['amount'];
            $return = (int) $value;
            $this->class->reflFields['amount']->setValue($document, $return);
            $hydratedData['amount'] = $return;
        }

        /** @Field(type="integer") */
        if (isset($data['persons'])) {
            $value = $data['persons'];
            $return = (int) $value;
            $this->class->reflFields['persons']->setValue($document, $return);
            $hydratedData['persons'] = $return;
        }

        /** @Field(type="integer") */
        if (isset($data['nights'])) {
            $value = $data['nights'];
            $return = (int) $value;
            $this->class->reflFields['nights']->setValue($document, $return);
            $hydratedData['nights'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['begin'])) {
            $value = $data['begin'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['begin']->setValue($document, clone $return);
            $hydratedData['begin'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['time'])) {
            $value = $data['time'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['time']->setValue($document, clone $return);
            $hydratedData['time'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['end'])) {
            $value = $data['end'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['end']->setValue($document, clone $return);
            $hydratedData['end'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['note'])) {
            $value = $data['note'];
            $return = (string) $value;
            $this->class->reflFields['note']->setValue($document, $return);
            $hydratedData['note'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['isCustomPrice'])) {
            $value = $data['isCustomPrice'];
            $return = (bool) $value;
            $this->class->reflFields['isCustomPrice']->setValue($document, $return);
            $hydratedData['isCustomPrice'] = $return;
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