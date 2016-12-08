<?php

namespace MBH\Bundle\BaseBundle\Twig;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HotelSelectorExtension extends \Twig_Extension
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
     * @return string
     */
    public function getName()
    {
        return 'mbh_hotel_selector_extension';
    }

    public function getSelectedHotel()
    {
        return $this->container->get('mbh.hotel.selector')->getSelected();
    }

    public function getHotels()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();

        $entities = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('h')
                       ->sort('fullTitle', 'asc')
                       ->getQuery()
                       ->execute()
        ;

        $result = [];
        foreach ($entities as $entity) {
            if ($this->checkPermissions($entity)) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    public function checkPermissions($doc)
    {
        return $this->container->get('mbh.hotel.selector')->checkPermissions($doc);
    }


    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'selected_hotel' => new \Twig_SimpleFunction('selected_hotel', [$this, 'getSelectedHotel'], array('is_safe' => array('html'))),
            'hotels' => new \Twig_SimpleFunction('hotels', [$this, 'getHotels'], array('is_safe' => array('html'))),
            'checkPermissions' => new \Twig_SimpleFunction('checkPermissions', [$this, 'checkPermissions'], array('is_safe' => array('html'))),
        );
    }

}
