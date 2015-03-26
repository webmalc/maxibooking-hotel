<?php
namespace MBH\Bundle\ChannelManagerBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChannelManagerSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface 
     */
    protected $container;
    
    /**
     * @var array 
     */
    private $flushedDocs = [];


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postFlush',
            'onFlush'
        );
    }
    
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $dm = $eventArgs->getDocumentManager();
        $uow = $dm->getUnitOfWork();

        $docs = array_merge(
            $uow->getScheduledDocumentUpdates(),
            $uow->getScheduledDocumentInsertions(),
            $uow->getScheduledDocumentDeletions()    
        );
        
        $classes = [
            '\MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface',
            '\MBH\Bundle\HotelBundle\Document\RoomType',
            '\MBH\Bundle\PriceBundle\Document\Tariff',
        ];

        foreach ($docs as $doc) {
            foreach ($classes as $class)  {
                if (is_a($doc, $class)) { 
                     $this->flushedDocs[] = $doc;
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if (!empty($this->flushedDocs)) {
            $this->sync();
        }
    }
    
    private function sync()
    {   
        try {
            $this->container->get('mbh.channelmanager')->syncInBackground();
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                var_dump($e); exit();
            }   
        }
    }
}
