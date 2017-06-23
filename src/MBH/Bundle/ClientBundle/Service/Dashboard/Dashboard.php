<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\Cursor;

/**
 * Class Dashboard - generates and list user news & notifications
 */
class Dashboard implements \SplSubject
{
    
    /**
     * @var \SplObjectStorage
     */
    private $sources;

    /**
     * @var ManagerRegistry
     */
    private $documentManager;
    
    /**
     * constructor
     */
    public function __construct(ManagerRegistry $documentManager)
    {
        $this->documentManager = $documentManager->getManager();
        $this->sources = new \SplObjectStorage();
    }

    /**
     * Attach dashboard source
     * @param \SplObserver $source
     */
    public function attach(\SplObserver $source)
    {
        $this->sources->attach($source);
    }

    /**
     * Detach dashboard source
     * @param \SplObserver $source
     */
    public function detach(\SplObserver $source)
    {
        $this->sources->detach($source);
    }

    /**
     * Notify all sources
     */
    public function notify()
    {
        foreach ($this->sources as $source) {
            $source->update($this);
        }
    }
    
    /**
     * get messages
     *
     * @return Cursor
     */
    public function getMessages(): Cursor
    {
        return $this->documentManager
            ->getRepository('MBHClientBundle:DashboardEntry')
            ->findNew();
    }
}
