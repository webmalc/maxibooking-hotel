<?php
namespace MBH\Bundle\RestaurantBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\RestaurantBundle\Document\Chair;
use MBH\Bundle\RestaurantBundle\Document\Table;

class TableManager
{
    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function generateChair($count, $type, Table $item)
    {
        if ($count) {
            for ($i = 0; $i < $count; $i++) {
                $chair = new Chair();
                $chair->setType($type);
                $chair->setTable($item);
                $this->dm->persist($chair);
            }
            $this->dm->flush();
        }
    }

}