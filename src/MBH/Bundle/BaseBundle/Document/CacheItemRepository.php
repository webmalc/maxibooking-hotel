<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class CacheItemRepository
 * @package MBH\Bundle\BaseBundle\Document
 */
class CacheItemRepository extends DocumentRepository
{
    /**
     * @param string $key
     * @return mixed
     */
    public function getByKey(string $key)
    {
        return $this->findBy(['key' => $key]);
    }

    /**
     * @param string $prefix
     * @return mixed
     */
    public function getKeysByPrefix(string $prefix): array
    {
        return array_map(function ($val) {
            return $val['key'];
        },
            iterator_to_array($this->createQueryBuilder()
                ->select('key')
                ->field('key')
                ->equals(new \MongoRegex('/^' . $prefix . '/i'))
                ->hydrate(false)
                ->getQuery()
                ->execute())
        );
    }

    /**
     * @param string $prefix
     * @return CacheItemRepository
     */
    public function deleteByPrefix(string $prefix): self
    {
        $this->createQueryBuilder()
            ->remove()
            ->field('key')->equals(new \MongoRegex('/^' . $prefix . '/i'))
            ->getQuery()
            ->execute()
        ;

        return $this;
    }
}
