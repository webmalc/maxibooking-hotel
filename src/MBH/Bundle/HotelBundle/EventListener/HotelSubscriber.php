<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 29.06.17
 * Time: 13:58
 */

namespace MBH\Bundle\HotelBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\ChannelManagerBundle\Services\TripAdvisor\TripAdvisorHelper;
use MBH\Bundle\ClientBundle\Service\Mbhs;
use MBH\Bundle\HotelBundle\Document\Hotel;

class HotelSubscriber implements EventSubscriber
{
    /** @var  Mbhs */
    private $mbhs;

    public function __construct(Mbhs $mbhs) {
        $this->mbhs = $mbhs;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
            Events::preRemove => 'preRemove'
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof Hotel) {
            $config = $args->getDocumentManager()
                ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
                ->findOneBy(['hotel' => $document]);
            if (!is_null($config)) {
                $this->mbhs->sendUpdateDataToMBHs($config);
            }
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof Hotel) {
            $config = $args->getDocumentManager()
                ->getRepository('MBHChannelManagerBundle:TripAdvisorConfig')
                ->findOneBy(['hotel' => $document]);
            $config->setIsEnabled(false);
            if (!is_null($config)) {
                $this->mbhs->sendUpdateDataToMBHs($config);
            }
        }
    }
}