<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\DocumentsRelationships;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\Relationship;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TariffManager
{
    private $dm;

    public function __construct(DocumentManager $dm) {
        $this->dm = $dm;
    }

    /**
     * @param Tariff $tariff
     * @throws DeleteException
     */
    public function forceDelete(Tariff $tariff)
    {
        $tariffRelationships = DocumentsRelationships::getRelationships()[Tariff::class];

        /** @var Relationship $tariffRelationship */
        foreach ($tariffRelationships as $tariffRelationship) {
            $repository = $this->dm->getRepository($tariffRelationship->getDocumentClass());
            if ($tariffRelationship->IsMany()) {
                $count = $repository->createQueryBuilder()
                    ->field($tariffRelationship->getFieldName())->includesReferenceTo($tariff)
                    ->field('deletedAt')->exists(false)
                    ->getQuery()
                    ->count();
            } else {
                $query = $repository->createQueryBuilder()
                    ->field($tariffRelationship->getFieldName())->references($tariff)
                    ->field('deletedAt')->exists(false)
                    ->getQuery();
                $count = $query->count();
            }
            if ($count > 0) {
                $message = $tariffRelationship->getErrorMessage() ? $tariffRelationship->getErrorMessage() : 'exception.relation_delete.message'; // have existing relation
                throw new DeleteException($message, $count);
            }
        }
        $tariff->setDeletedAt(new \DateTime());
        $this->dm->flush();
    }
}