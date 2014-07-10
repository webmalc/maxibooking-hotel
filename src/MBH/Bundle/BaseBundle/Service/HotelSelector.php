<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * HotelSelector service
 */
class HotelSelector
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    public function getSelected()
    {
        $session = $this->container->get('session');
        $id = $session->get('selected_hotel_id');

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container
                ->get('doctrine_mongodb')
                ->getManager()
        ;

        if (!empty($id)) {
            $hotel = $dm->getRepository('MBHHotelBundle:Hotel')
                    ->find($id)
            ;
            if ($hotel) {
                return $hotel;
            }

            $session->remove('selected_hotel_id');
        }

        // Select first hotel
        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('s')
                ->limit(1)
                ->sort('isDefault', 'desc')
                ->getQuery()
                ->getSingleResult()
        ;

        if ($hotel) {
            $session->set('selected_hotel_id', (string) $hotel->getId());
            return $hotel;
        }

        return null;
    }

    /**
     * @param string $id
     * @return @return \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    public function setSelected($id)
    {
        $session = $this->container->get('session');
        $session->remove('selected_hotel_id');
        
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container
                ->get('doctrine_mongodb')
                ->getManager()
        ;

        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')
                ->find($id)
        ;
        if ($hotel) {
            $session->set('selected_hotel_id', (string) $hotel->getId());
            return $hotel;
        }
        
        return null;
    }

}
