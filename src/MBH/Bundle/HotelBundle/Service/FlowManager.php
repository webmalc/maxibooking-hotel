<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
use MBH\Bundle\HotelBundle\Form\MBSiteFlow\MBSiteFlow;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlowManager
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    const FLOW_SETTINGS = [
        HotelFlow::FLOW_TYPE => [
            'service' => 'mbh.hotel_flow',
            'ROLE' => 'ROLE_HOTEL_FLOW'
        ],
        RoomTypeFlow::FLOW_TYPE => [
            'service' => 'mbh.room_type_flow',
            'ROLE' => 'ROLE_ROOMTYPE_FLOW'
        ],
        MBSiteFlow::FLOW_TYPE => [
            'service' => 'mbh.mb_site_flow',
            'ROLE' => 'ROLE_MB_SITE_FLOW'
        ]
    ];

    /**
     * @param string $flowType
     * @return FormFlow|object
     */
    public function getServiceByFlowType(string $flowType)
    {
        if (!isset(self::FLOW_SETTINGS[$flowType]['service'])) {
            throw new \InvalidArgumentException($flowType
                . ' settings is not defined. Please, specify them in FLOW_SETTINGS constant of FlowManager class');
        }

        $serviceTitle = self::FLOW_SETTINGS[$flowType]['service'];

        return $this->container->get($serviceTitle);
    }

    public function getProgressRate()
    {
        //TODO:
    }
}