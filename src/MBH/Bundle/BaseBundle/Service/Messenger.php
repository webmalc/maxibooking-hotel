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
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
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
     * @param $text
     * @param string $from
     * @param string $type
     * @param bool $autohide
     * @param null $end
     * @param bool $email
     * @return bool
     */
    public function send($text, $from = 'system', $type = 'info', $autohide = false, $end = null, $email = false)
    {
        $this->clear($from);
        $message = new Message();
        $message->setFrom($from)
            ->setText($text)
            ->setType($type)
            ->setAutohide($autohide)
            ->setEnd($end)
        ;
        $this->dm->persist($message);
        $this->dm->flush();

        if ($email) {
            $qb = $this->dm->getRepository('MBHUserBundle:User')->createQueryBuilder('s');
            $qb->field('enabled')->equals(true)
                ->field('locked')->equals(false)
                ->field('roles')->in(['ROLE_ADMIN']);
            ;
            foreach ($qb->getQuery()->execute() as $user) {
                $emails[] = $user->getEmail();
            }
            try {
                $this->container->get('mbh.mailer')->send($emails, ['text' => $text]);
            } catch (\Exception $e) {

            }
        }

        return true;
    }

    /**
     * @param string $from
     */
    public function clear($from = 'system')
    {
        $this->dm->getRepository('MBHBaseBundle:Message')
            ->createQueryBuilder('q')
            ->remove()
            ->field('from')->equals($from)
            ->getQuery()
            ->execute()
        ;
    }
}
