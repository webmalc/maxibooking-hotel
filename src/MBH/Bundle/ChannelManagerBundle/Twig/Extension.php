<?php
namespace MBH\Bundle\ChannelManagerBundle\Twig;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\ChannelManagerBundle\Services\CMWizardManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Extension extends \Twig_Extension
{

    /**
    * @var \Doctrine\ODM\MongoDB\DocumentManager
    */
    protected $docManager;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    
    /**
     * @var array
     */
    private $serviceParams;
    private $channelManagerWizard;
    
    /**
     * @var array|null
     */
    private $notifications = null;

    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $docManager,
        array $serviceParams,
        CMWizardManager $channelManagerWizard
    ) {
        $this->docManager = $docManager;
        $this->container = $container;
        $this->serviceParams = $serviceParams;
        $this->channelManagerWizard = $channelManagerWizard;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mbh_twig_chanelmanager_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'mbh_channelmanager_get_notifications' => new \Twig_SimpleFunction(
                'mbh_channelmanager_get_notifications',
                [$this, 'serviceNotifications'],
                ['is_safe' => ['html']]
            ),
            'channel_manager_human_name' => new \Twig_SimpleFunction(
                'channel_manager_human_name',
                [$this, 'getChannelManagerHumanName'],
                ['is_safe' => ['html']]
            ),
            'is_cm_configured_by_tech_support' => new \Twig_SimpleFunction(
                'is_cm_configured_by_tech_support',
                [$this, 'isChannelManagerConfiguredByTechSupport'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * @return array
     */
    public function serviceNotifications(ChannelManagerConfigInterface $config): array
    {
        if ($this->notifications === null) {
            $service = $this->container->get(
                $this->serviceParams[$config->getName()]['service']
            );
            $this->notifications = $service->getNotifications($config);
        }

        return $this->notifications;
    }

    /**
     * @param string $serviceName
     * @return string
     */
    public function getChannelManagerHumanName(string $serviceName)
    {
        return $this->serviceParams[$serviceName]['title'];
    }

    /**
     * @param string $channelManagerName
     * @return bool
     */
    public function isChannelManagerConfiguredByTechSupport(string $channelManagerName)
    {
        return $this->channelManagerWizard->isConfiguredByTechSupport($channelManagerName);
    }
}
