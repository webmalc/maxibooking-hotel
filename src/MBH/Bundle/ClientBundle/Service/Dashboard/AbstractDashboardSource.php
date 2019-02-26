<?php

namespace MBH\Bundle\ClientBundle\Service\Dashboard;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\ClientBundle\Document\DashboardEntry;
use MBH\Bundle\BaseBundle\Service\Helper;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AbstractDashboardSource class
 */
abstract class AbstractDashboardSource implements \SplObserver
{
    
    /**
     * route - url route
     */
    const ROUTE = null;

    /**
     * message default type
     */
    const TYPE = 'info';

    /**
     * @var ManagerRegistry
     */
    protected $documentManager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;
    
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Helper
     */
    protected $helper;

    protected $roomTypeKey;

    /**
     * constructor
     */
    public function __construct(
        ManagerRegistry $documentManager,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Helper $helper
    ) {
    
        $this->documentManager = $documentManager->getManager();
        $this->validator = $validator;
        $this->translator = $translator;
        $this->helper = $helper;
        $this->roomTypeKey = $documentManager->getRepository(ClientConfig::class)->fetchConfig()->getUseRoomTypeCategory() ? 'roomTypeCategory': 'roomType';
    }
    
    /**
     * Save message to DB
     *
     * @param string $message
     * @param string $type
     * @param string|null $route
     * @return self
     */
    protected function saveMessage(string $message, string $type, string $route = null): self
    {
        $entry = new DashboardEntry();
        $entry->setText($message)
            ->setType($type)
            ->setRoute($route)
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
        foreach ($this->generateMessages() as $message) {
            $this->saveMessage($message, static::TYPE, static::ROUTE);
        }
    }
}
