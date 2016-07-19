<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class MBHBundlePackageBundleDocumentOrderPollQuestionHydrator implements HydratorInterface
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
        if (isset($data['question'])) {
            $reference = $data['question'];
            if (isset($this->class->fieldMappings['question']['simple']) && $this->class->fieldMappings['question']['simple']) {
                $className = $this->class->fieldMappings['question']['targetDocument'];
                $mongoId = $reference;
            } else {
                $className = $this->unitOfWork->getClassNameForAssociation($this->class->fieldMappings['question'], $reference);
                $mongoId = $reference['$id'];
            }
            $targetMetadata = $this->dm->getClassMetadata($className);
            $id = $targetMetadata->getPHPIdentifierValue($mongoId);
            $return = $this->dm->getReference($className, $id);
            $this->class->reflFields['question']->setValue($document, $return);
            $hydratedData['question'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['code'])) {
            $value = $data['code'];
            $return = (string) $value;
            $this->class->reflFields['code']->setValue($document, $return);
            $hydratedData['code'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['value'])) {
            $value = $data['value'];
            $return = (string) $value;
            $this->class->reflFields['value']->setValue($document, $return);
            $hydratedData['value'] = $return;
        }

        /** @Field(type="boolean") */
        if (isset($data['isQuestion'])) {
            $value = $data['isQuestion'];
            $return = (bool) $value;
            $this->class->reflFields['isQuestion']->setValue($document, $return);
            $hydratedData['isQuestion'] = $return;
        }
        return $hydratedData;
    }
}