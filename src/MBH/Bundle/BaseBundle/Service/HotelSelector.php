<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;

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
     * @param Hotel $hotel
     * @return bool
     */
    public function checkPermissions(Hotel $hotel)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $securityContext->isGranted('EDIT', $hotel);
    }

    /**
     * @return null|\MBH\Bundle\HotelBundle\Document\Hotel
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
            if ($hotel && $this->checkPermissions($hotel)) {
                return $hotel;

            }
            $session->remove('selected_hotel_id');
        }

        // Select first hotel
        $hotels = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('s')
                ->sort('isDefault', 'desc')
                ->getQuery()
                ->execute()
        ;

        foreach ($hotels as $hotel) {
            if ($hotel && $this->checkPermissions($hotel)) {
                $session->set('selected_hotel_id', (string) $hotel->getId());
                return $hotel;
            }
        }
        return null;
    }


    /**
     * @param string $id
     * @return \MBH\Bundle\HotelBundle\Document\Hotel
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
        if ($hotel && $this->checkPermissions($hotel)) {
            $session->set('selected_hotel_id', (string) $hotel->getId());
            return $hotel;
        }
        
        return null;
    }

    /**
     * @return array
     */
    public function getSelectedPackages()
    {
        $hotel = $this->getSelected();

        if (!$hotel) {
            return [];
        }

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');

        $packages = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('s')
            ->field('roomType.id')->in($this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()))
            ->getQuery()
            ->execute()
        ;
        $dm->getFilterCollection()->enable('softdeleteable');

        return $packages;
    }

}
