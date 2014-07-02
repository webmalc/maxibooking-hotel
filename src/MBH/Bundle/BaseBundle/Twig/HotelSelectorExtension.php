<?php

namespace MBH\Bundle\BaseBundle\Twig;

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
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        
        $entities = $dm->getRepository('MBHHotelBundle:Hotel')->createQueryBuilder('h')
                       ->sort('fullTitle', 'asc')
                       ->getQuery()
                       ->execute()
        ;
        
        return $entities;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'selected_hotel' => new \Twig_Function_Method($this, 'getSelectedHotel', array('is_safe' => array('html'))),
            'hotels' => new \Twig_Function_Method($this, 'getHotels', array('is_safe' => array('html'))),
        );
    }

}
