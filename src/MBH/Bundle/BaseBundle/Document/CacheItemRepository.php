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
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @return $this
     */
    public function getQuery(string $prefix, \DateTime $begin = null, \DateTime $end = null)
    {

        $qb = $this->createQueryBuilder()
            ->field('key')->equals(new \MongoRegex('/^' . $prefix . '/i'));

        if ($begin) {
            $qb->field('end')->gte($begin);
        }
        if ($end) {
            $qb->field('begin')->lte($end);
        }

        return $qb;
    }

    /**
     * @param string $prefix
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @return array
     */
    public function getKeysByPrefix(string $prefix, \DateTime $begin = null, \DateTime $end = null): array
    {
        $qb = $this->getQuery($prefix, $begin, $end);

        $caches = $qb->select(['key', 'begin', 'end'])->hydrate(false)->getQuery()->execute();

        return array_map(
            function ($val) use ($caches) {
                return $val['key'];
            },
            iterator_to_array($caches)
        );
    }
    
    /**
     * clear expired items
     *
     * @param type $param
     * @return int
     */
    public function clearExpiredItems(): int
    {
        return $this->createQueryBuilder()
            ->field('lifetime')->exists(true)
            ->field('lifetime')->notEqual(null)
            ->field('lifetime')->lte(new \DateTime())
            ->remove()
            ->getQuery()
            ->execute()['n'];
    }

    /**
     * @param string $prefix
     * @param \DateTime|null $begin
     * @param \DateTime|null $end
     * @return int
     */
    public function deleteByPrefix(string $prefix, \DateTime $begin = null, \DateTime $end = null): int
    {
        $qb = $this->getQuery($prefix, $begin, $end);

        return $qb->remove()->getQuery()->execute()['n'];
    }
}
