<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 11.05.17
 * Time: 14:09
 */

namespace MBH\Bundle\CashBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\BaseBundle\Lib\AbstractFixture;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Order;

/**
 * Class CashDocumentData
 * @package MBH\Bundle\CashBundle\DataFixtures\MongoDB
 */
class CashDocumentData extends AbstractFixture implements OrderedFixtureInterface
{
    const CASH_DOCUMENTS_DATA = [
        ['orderNumber' => 1, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => true],
        ['orderNumber' => 2, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => true],
        ['orderNumber' => 3, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => true],
        ['orderNumber' => 4, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => true],
        ['orderNumber' => 5, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => true],
        ['orderNumber' => 6, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => false],
        ['orderNumber' => 7, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => false],
        ['orderNumber' => 8, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => false],
        ['orderNumber' => 9, 'method' => 'cash', 'operation' => 'in', 'isConfirmed' => false],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function doLoad(ObjectManager $manager)
    {
        $this->persistCashDocuments($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    public function persistCashDocuments(ObjectManager $manager)
    {
        foreach (self::CASH_DOCUMENTS_DATA as $cashDocumentData) {
            /** @var Order $order */
            $order = $this->getReference('order' . $cashDocumentData['orderNumber']);
            if ($order->getPaid()) {
                $cashDocument = (new CashDocument())
                    ->setOrder($order)
                    ->setMethod($cashDocumentData['method'])
                    ->setOperation($cashDocumentData['operation'])
                    ->setIsConfirmed($cashDocumentData['isConfirmed'])
                    ->setTotal($order->getPaid())
                    ->setPaidDate($order->getCreatedAt())
                    ->setTouristPayer($order->getMainTourist());

                $manager->persist($cashDocument);
                $this->setReference('cash_doc' . $cashDocumentData['orderNumber'], $cashDocument);
            }
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 400;
    }

    /**
     * {@inheritDoc}
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev'];
    }
}