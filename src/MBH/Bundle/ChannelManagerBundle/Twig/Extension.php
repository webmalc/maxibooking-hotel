<?php
namespace MBH\Bundle\ChannelManagerBundle\Twig;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
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

    
    /**
     * @var array|null
     */
    private $notifications = null;

    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $docManager,
        array $serviceParams
    ) {
        $this->docManager = $docManager;
        $this->container = $container;
        $this->serviceParams = $serviceParams;
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
}
