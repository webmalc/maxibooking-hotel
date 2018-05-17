<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\Base;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

class DocumentsCollectionFieldType implements DBNormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass) {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @return array
     * @throws InvalidArgumentException
     */
    public function normalize($value)
    {
        $this->checkIsIterable($value);

        return array_map(function (Base $document) {
            return $document->getId();
        }, (array)$value);
    }

    /**
     * @param $value
     * @param DocumentManager $dm
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws InvalidArgumentException
     */
    public function denormalize($value, DocumentManager $dm)
    {
        $this->checkIsIterable($value);

        /** @var DocumentRepository $documentRepo */
        $documentRepo = $dm->getRepository($this->documentClass);

        return $documentRepo
            ->createQueryBuilder()
            ->field('id')->in($value)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @param $value
     * @throws InvalidArgumentException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new InvalidArgumentException('Passed value is not iterable');
        }
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->documentClass;
    }
}