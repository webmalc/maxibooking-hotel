<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\Cursor;
use MBH\Bundle\ClientBundle\Document\DashboardEntryRepository;

/**
 * Class Dashboard - generates and list user news & notifications
 */
class Dashboard implements \SplSubject
{
    /**
     * message default lifetime
     */
    const LIFETIME = 10;

    /**
     * @var \SplObjectStorage
     */
    private $sources;

    /**
     * @var ManagerRegistry
     */
    private $documentManager;

    /**
     * @var DashboardEntryRepository
     */
    private $repo;
    
    /**
     * constructor
     */
    public function __construct(ManagerRegistry $documentManager)
    {
        $this->documentManager = $documentManager->getManager();
        $this->sources = new \SplObjectStorage();
        $this->repo = $this->documentManager
            ->getRepository('MBHClientBundle:DashboardEntry');
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
        $this->repo->remove(new \DateTime('-' . static::LIFETIME . ' days'));
 
        foreach ($this->sources as $source) {
            $source->update($this);
        }
    }

    /**
     * Clear messages
     *
     * @return self
     */
    public function clear(): self
    {
        $this->repo->remove();
        
        return $this;
    }
    
    /**
     * Get messages
     *
     * @return Cursor
     */
    public function getMessages(): Cursor
    {
        return $this->repo->findNew();
    }
}
