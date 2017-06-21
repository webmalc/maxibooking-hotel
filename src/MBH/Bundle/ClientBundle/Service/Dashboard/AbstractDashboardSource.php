<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\ClientBundle\Document\DashboardEntry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AbstractDashboardSource class
 */
abstract class AbstractDashboardSource implements \SplObserver
{
    /**
     * message default type
     */
    const TYPE = 'info';

    /**
     * message default lifetime
     */
    const LIFETIME = 10;

    /**
     * @var ManagerRegistry
     */
    private $documentManager;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * constructor
     */
    public function __construct(ManagerRegistry $documentManager, ValidatorInterface $validator)
    {
        $this->documentManager = $documentManager->getManager();
        $this->validator = $validator;
    }
    
    /**
     * Save message to DB
     *
     * @param string $message
     * @param string $type
     * @return self
     */
    protected function saveMessage(string $message, string $type): self
    {
        $entry = new DashboardEntry();
        $entry->setText($message)
            ->setType($type)
            ->setSource(get_class($this));

        if (!count($this->validator->validate($entry))) {
            $this->documentManager->persist($entry);
            $this->documentManager->flush();
        }

        return $this;
    }
    
    /**
     * Generate messages
     *
     * @param type $param
     * @return array
     */
    protected function generateMessages(): array
    {
        return [];
    }
    
    /**
     * Generate & save messages
     *
     * @param type $dashboard
     */
    public function update(\SplSubject $dashboard)
    {
        $this->documentManager
            ->getRepository('MBHClientBundle:DashboardEntry')
            ->remove(new \DateTime('-' . static::LIFETIME . ' days'));

        foreach ($this->generateMessages() as $message) {
            $this->saveMessage($message, static::TYPE);
        }
    }
}
