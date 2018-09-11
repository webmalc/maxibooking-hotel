<?php

namespace MBH\Bundle\PriceBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\DocumentsRelationships;
use MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber\Relationship;
use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\PriceBundle\Document\Tariff;

class TariffManager
{
    private $dm;
    private $helper;

    public function __construct(DocumentManager $dm, Helper $helper) {
        $this->dm = $dm;
        $this->helper = $helper;
    }

    /**
     * @param Tariff $tariff
     * @throws DeleteException
     */
    public function forceDelete(Tariff $tariff)
    {
        $relatedDocumentsData = $this->helper->getRelatedDocuments($tariff);

        foreach ($relatedDocumentsData as $relatedDocumentData) {
            $quantity = $relatedDocumentData['quantity'];
            /** @var Relationship $relation */
            $relation = $relatedDocumentData['relation'];

            if ($quantity > 0) {
                $message = $relation->getErrorMessage() ? $relation->getErrorMessage() : 'exception.relation_delete.message'; // have existing relation
                throw new DeleteException($message, $quantity);
            }
        }

        $tariff->setDeletedAt(new \DateTime());
        $this->dm->flush();
    }
}