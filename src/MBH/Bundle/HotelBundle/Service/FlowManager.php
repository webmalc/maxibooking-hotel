<?php

namespace MBH\Bundle\HotelBundle\Service;

use MBH\Bundle\HotelBundle\Document\FlowConfig;
use MBH\Bundle\HotelBundle\Form\HotelFlow\HotelFlow;
use MBH\Bundle\HotelBundle\Form\MBSiteFlow\MBSiteFlow;
use MBH\Bundle\HotelBundle\Form\RoomTypeFlow\RoomTypeFlow;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlowManager
{
    private $container;
    private $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine.odm.mongodb.document_manager');
    }

    const FLOW_SETTINGS = [
        HotelFlow::FLOW_TYPE => [
            'service' => 'mbh.hotel_flow',
            'role' => 'ROLE_HOTEL_FLOW',
            'template' => '@MBHHotel/Flow/hotelFlow.html.twig'
        ],
        RoomTypeFlow::FLOW_TYPE => [
            'service' => 'mbh.room_type_flow',
            'role' => 'ROLE_ROOMTYPE_FLOW',
            'template' => '@MBHHotel/Flow/roomTypeFlow.html.twig'
        ],
        MBSiteFlow::FLOW_TYPE => [
            'service' => 'mbh.mb_site_flow',
            'role' => 'ROLE_MB_SITE_FLOW',
            'template' => '@MBHHotel/Flow/mbSiteFlow.html.twig'
        ]
    ];

    /**
     * @param string $flowType
     * @return string
     */
    public function getFlowRole(string $flowType)
    {
        return $this->getFlowSettings($flowType)['role'];
    }

    public function getFlowTemplate(string $flowType)
    {
        return $this->getFlowSettings($flowType)['template'];
    }

    /**
     * @param string $flowType
     * @return FormFlow|object
     */
    public function getServiceByFlowType(string $flowType)
    {
        $serviceTitle = $this->getFlowSettings($flowType)['service'];

        return $this->container->get($serviceTitle);
    }

    public function initFlowService(string $flowType, string $flowId = null)
    {
        return $this
            ->getServiceByFlowType($flowType)
            ->init($flowId);
    }

    /**
     * @param string $flowType
     * @param array $flowIds
     * @return array
     */
    public function getProgressRateByFlowId(string $flowType, array $flowIds)
    {
        $numberOfSteps = $this
            ->initFlowService($flowType)
            ->getNumberOfSteps();

        $flowConfigs = $this->dm
            ->getRepository(FlowConfig::class)
            ->findBy([
                'flowType' => $flowType,
                'flowId' => ['$in' => $flowIds]
            ]);

        /** @var FlowConfig[] $configsByFlowIds */
        $configsByFlowIds = $this->container
            ->get('mbh.helper')
            ->sortByValue($flowConfigs, false, 'getFlowId');

        $result = [];
        foreach ($flowIds as $flowId) {
            $config = $configsByFlowIds[$flowId] ?? null;
            $rate = $this->calcFlowProgressRate($config, $numberOfSteps);
            $result[$flowId] = $rate;
        }

        return $result;
    }

    /**
     * @param string $flowType
     * @return array
     */
    private function getFlowSettings(string $flowType)
    {
        if (!isset(self::FLOW_SETTINGS[$flowType])) {
            throw new \InvalidArgumentException($flowType
                . ' settings is not defined. Please, specify them in FLOW_SETTINGS constant of FlowManager class');
        }

        return self::FLOW_SETTINGS[$flowType];
    }

    /**
     * @param FlowConfig|null $config
     * @param $numberOfSteps
     * @return float|int
     */
    public function calcFlowProgressRate(?FlowConfig $config, $numberOfSteps)
    {
        if (is_null($config)) {
            return 0;
        }

        return round(($config->getCurrentStepNumber() - 1) / $numberOfSteps, 2) * 100;
    }
}