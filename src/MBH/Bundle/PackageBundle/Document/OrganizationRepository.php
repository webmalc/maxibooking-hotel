<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class OrganizationRepository
 * @package MBH\Bundle\PackageBundle\Document
 */
class OrganizationRepository extends DocumentRepository
{

    /**
     * @param array $hotels
     * @return Organization|null
     */
    public function fetchOne(array $hotels)
    {
        return $this->getQueryBuilder($hotels)->getQuery()->getSingleResult();
    }

    /**
     * @param array $hotels
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    public function getQueryBuilder(array $hotels)
    {
        $qb = $this->createQueryBuilder('q');

        if (empty($hotels)) {
            $qb->field('hotel.id')->in($hotels);
        }
        return $qb;
    }

    /**
     * @param Order $order
     * @return Organization|null
     */
    public function getOrganizationByOrder(Order $order)
    {
        /** @var Package[] $packages */
        $packages = $order->getPackages();
        $ids = [];
        foreach ($packages as $package) {
            $ids[] = $package->getRoomType()->getHotel()->getId();
        }

        return $this->fetchOne($ids);
    }

    /**
     * @param string $query
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getContragentsIdsByQueryString(string $query): array
    {
        return $this
            ->createQueryBuilder()
            ->field('type')->equals('contragents')
            ->field('name')->equals(new \MongoRegex('/^.*' . $query . '.*/ui'))
            ->distinct('id')
            ->getQuery()
            ->execute()
            ->toArray()
        ;
    }

    /**
     * @return Organization|null|object
     */
    public function getForFmsExport()
    {
        return $this->findOneBy(['type' => 'my']);
    }
}