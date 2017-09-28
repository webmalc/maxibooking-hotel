<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use MBH\Bundle\BaseBundle\Document\Message;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Messenger service
 */
class Messenger implements \SplObserver
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

    /**
     * @param \SplSubject $notifier
     */
    public function update(\SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        $message = $notifier->getMessage();

        $this->send($message->getText(), $message->getFrom(), $message->getType(), $message->getAutohide(),
            $message->getEnd(), $message->getCategory(), $message->getHotel());

    }

    /**
     * @param $text
     * @param string $from
     * @param string $type
     * @param bool $autohide
     * @param null $end
     * @param null $category
     * @param Hotel|null $hotel
     * @return Messenger
     */
    public function send($text, $from = 'system', $type = 'info', $autohide = false, $end = null, $category = null, Hotel $hotel = null)
    {
        return $this->add($text, $from, $type, $autohide, $end, $category, $hotel);
    }

    /**
     * Print messages
     */
    public function get()
    {
        $messages = $this->dm->getRepository('MBHBaseBundle:Message')->findAll();
        $session = $this->container->get('session');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $permissions = $this->container->get('mbh.hotel.selector');

        foreach ($messages as $message) {


            $method = 'get' . ucfirst($message->getCategory()) . 's';

            if (!$message->getCategory() || !$user || !method_exists($user, $method) || $user->$method()) {

                if ($message->getHotel() && !$permissions->checkPermissions($message->getHotel())) {
                    continue;
                }

                $key[0] = $message->getType();
                $key[1] = $message->getAutohide();
                $session->getFlashBag()->add(implode('|', $key), $message->getText());
                $message->setIsSend(true);
                $this->dm->persist($message);
                $this->dm->flush($message);
            }
        }

        $this->clear();
    }

    /**
     * @param $text
     * @param string $from
     * @param string $type
     * @param bool $autohide
     * @param null $end
     * @param null $category
     * @param Hotel|null $hotel
     * @return $this
     */
    public function add($text, $from = 'system', $type = 'info', $autohide = false, $end = null, $category = null, Hotel $hotel = null)
    {
        $message = new Message();
        $message->setFrom($from)
            ->setText($text)
            ->setType($type)
            ->setAutohide($autohide)
            ->setEnd($end)
            ->setCategory($category)
            ->setHotel($hotel)
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
            ->createQueryBuilder()
            ->field('isSend')->equals(true)
            ->remove();
        if ($from) {
            $qb->field('from')->equals($from);
        }

        $qb->getQuery()->execute();

        return $this;
    }
}
