<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Document\Message;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Messenger service
 */
class Messenger
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
    }

    public function send($text, $from = 'system', $type = 'info', $autohide = false, $end = null, $email = false)
    {
        return $this->add($text, $from, $type, $autohide, $end);
    }

    /**
     * Print messages
     */
    public function get()
    {
        $messages = $this->dm->getRepository('MBHBaseBundle:Message')->findAll();
        $session = $this->container->get('session');

        foreach($messages as $message) {
            $session->getFlashBag()->add($message->getType(), $message->getText());
        }
        $this->clear();
    }

    /**
     * Add message
     * @param $text
     * @param string $from
     * @param string $type
     * @param bool $autohide
     * @param null $end
     * @return $this
     */
    public function add($text, $from = 'system', $type = 'info', $autohide = false, $end = null)
    {
        $message = new Message();
        $message->setFrom($from)
            ->setText($text)
            ->setType($type)
            ->setAutohide($autohide)
            ->setEnd($end)
        ;
        $this->dm->persist($message);
        $this->dm->flush();

        return $this;
    }

    /**
     * @param null $from
     * @return $this
     */
    public function clear($from = null)
    {
        $qb = $this->dm->getRepository('MBHBaseBundle:Message')
            ->createQueryBuilder('q')
            ->remove()
        ;
        if ($from) {
            $qb->field('from')->equals($from);
        }

        $qb->getQuery()->execute();

        return $this;
    }
}
