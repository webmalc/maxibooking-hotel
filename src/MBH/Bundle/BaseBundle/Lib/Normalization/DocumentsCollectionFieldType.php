<?php

namespace MBH\Bundle\BaseBundle\Lib\Normalization;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Service\Utils;

class DocumentsCollectionFieldType implements NormalizableInterface
{
    private $documentClass;

    public function __construct(string $documentClass) {
        $this->documentClass = $documentClass;
    }

    /**
     * @param $value
     * @param array $options
     * @return array
     * @throws NormalizationException
     */
    public function normalize($value, array $options = [])
    {
        $this->checkIsIterable($value);

        return array_map(function ($document) {
            if (!$document instanceof Base) {
                throw new NormalizationException(Utils::getStringValueOrType($document) . ' is not an instance of Base');
            }

            return $document->getId();
        }, $this->castToArray($value));
    }

    /**
     * @param $value
     * @param array $options
     * @return mixed
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws NormalizationException
     */
    public function denormalize($value, array $options)
    {
        $this->checkIsIterable($value);
        $dm = $options['dm'];

        /** @var DocumentRepository $documentRepo */
        $documentRepo = $dm->getRepository($this->documentClass);

        return array_values($documentRepo
            ->createQueryBuilder()
            ->field('id')->in($value)
            ->getQuery()
            ->execute()
            ->toArray());
    }

    /**
     * @param $value
     * @return array
     */
    private function castToArray($value)
    {
        return is_array($value) ? $value : iterator_to_array($value);
    }

    /**
     * @param $value
     * @throws NormalizationException
     */
    private function checkIsIterable($value)
    {
        if (!is_iterable($value)) {
            throw new NormalizationException('Passed value is not iterable');
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