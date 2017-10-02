<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Notifier service
 */
class Notifier implements \SplSubject
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \SplObjectStorage
     */
    private $observers;

    /**
     * @var NotifierMessage
     */
    private $message;

    /** @var  Logger */
    private $logger;

    public function __construct(ContainerInterface $container, Logger $logger)
    {
        $this->container = $container;
        $this->observers = new \SplObjectStorage();
        $this->logger = $logger;
    }

    /**
     * @return NotifierMessage
     */
    public static function createMessage()
    {
        return new NotifierMessage();
    }

    /**
     * @param \SplObserver $observer
     * @return $this
     */
    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);

        return $this;
    }

    /**
     * @param NotifierMessage $message
     * @return $this
     */
    public function setMessage(NotifierMessage $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return NotifierMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \SplObserver $observer
     * @return $this
     */
    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);

        return $this;
    }


    public function notify()
    {
        foreach ($this->observers as $observer) {
            try {
                $this->logger->addInfo('Try to update '.get_class($observer).' message observer');
                $observer->update($this);
            } catch (\Throwable $e) {
                $this->logger->addCritical($e->getMessage());
                $env = $this->container->get('kernel')->getEnvironment();
                if ($env == 'dev') {
                    dump($e->getMessage());
                } elseif ($env == 'test' && php_sapi_name() == 'cli') {
                    echo $e;
                }
                throw $e;
            }
        }

        $this->setMessage(null);

        return $this;
    }
}
