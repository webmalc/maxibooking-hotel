<?php


namespace MBH\Bundle\BaseBundle\Lib\DocumentTraits;

use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

/**
 * Trait FindAllRawTrait
 * @package MBH\Bundle\BaseBundle\Lib\DocumentTraits
 * @method QueryBuilder createQueryBuilder
 *
 */
trait FindAllRawTrait
{
    public function findAllRaw(): array
    {
        $qb = $this->createQueryBuilder();

        return $qb->hydrate(false)->getQuery()->toArray();
    }
}