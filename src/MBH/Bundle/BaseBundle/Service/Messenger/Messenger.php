<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Doctrine\DBAL\Query\QueryBuilder;
use MBH\Bundle\BaseBundle\Document\Message;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
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
        /** @var Notifier $notifier */
        $message = $notifier->getMessage();

        $currentMessageText = is_null($message->getTextHtmlLink()) ? $message->getText() : $message->getTextHtmlLink();

        $messageText = $this->container->get('translator')->trans($currentMessageText, $message->getTranslateParams());

        $this->send(
            $messageText,
            $message->getFrom(),
            $message->getType(),
            $message->getAutohide(),
            $message->getEnd(),
            $message->getCategory(),
            $message->getHotel(),
            $message->getMessageType()
        );
    }

    /**
     * @param $text
     * @param string $from
     * @param string $type
     * @param bool $autohide
     * @param null $end
     * @param null $category
     * @param Hotel|null $hotel
     * @param string $messageType
     * @return Messenger
     */
    public function send(
        $text,
        $from = 'system',
        $type = 'info',
        $autohide = false,
        $end = null,
        $category = null,
        Hotel $hotel = null,
        string $messageType
    ) {
        return $this->add($text, $from, $type, $autohide, $end, $category, $hotel, $messageType);
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

            /** @var Message $message */
            $messageType = $message->getMessageType();
            /** @var User $user */
            if ($user instanceof User && $user->isNotificationTypeExists($messageType)) {
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
     * @param null $messageType
     * @return $this
     */
    public function add(
        $text,
        $from = 'system',
        $type = 'info',
        $autohide = false,
        $end = null,
        $category = null,
        Hotel $hotel = null,
        $messageType = null
    ) {
        $message = new Message();
        $message->setFrom($from)
            ->setText($text)
            ->setType($type)
            ->setAutohide($autohide)
            ->setEnd($end)
            ->setCategory($category)
            ->setHotel($hotel)
            ->setMessageType($messageType);
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
