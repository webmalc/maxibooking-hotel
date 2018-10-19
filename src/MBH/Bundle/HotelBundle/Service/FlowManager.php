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
    private $helper;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine.odm.mongodb.document_manager');
        $this->helper = $container->get('mbh.helper');
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
     * @return array
     */
    public function getAverageProgressRatesByTypes()
    {
        $result = [];
        foreach (array_keys(self::FLOW_SETTINGS) as $flowType) {
            $flowIds = $this->getFlowIdsForType($flowType);
            $progressRates = $this->getProgressRateByFlowIds($flowType, $flowIds);
            $averageProgress = array_sum(array_values($progressRates)) / count($progressRates);
            $result[$flowType] = round($averageProgress);
        }

        return $result;
    }

    /**
     * @param string $flowType
     * @param array $flowIds
     * @return array
     */
    public function getProgressRateByFlowIds(string $flowType, array $flowIds)
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
        $configsByFlowIds = $this->helper->sortByValue($flowConfigs, false, 'getFlowId');

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
        if ($config->isFinished()) {
            return 100;
        }

        return round(($config->getCurrentStepNumber() - 1) / $numberOfSteps, 2) * 100;
    }

    /**
     * @param $flowType
     * @return array
     */
    protected function getFlowIdsForType($flowType): array
    {
        switch ($flowType) {
            case RoomTypeFlow::FLOW_TYPE:
                $roomTypes = $this->dm->getRepository('MBHHotelBundle:RoomType')->findBy(['isEnabled' => true]);

                return $this->helper->toIds($roomTypes);
            case HotelFlow::FLOW_TYPE:
                $hotels = $this->dm->getRepository('MBHHotelBundle:Hotel')->findBy(['isEnabled' => true]);

                return $this->helper->toIds($hotels);
            case MBSiteFlow::FLOW_TYPE:
                $siteConfig = $this->dm->getRepository('MBHOnlineBundle:SiteConfig')->findOneBy([]);
                if (!is_null($siteConfig)) {
                    return [$siteConfig->getId()];
                }

                return [];
            default:
                throw new \InvalidArgumentException('Flow ids are not specified for flow with type ' . $flowType);
        }
    }
}