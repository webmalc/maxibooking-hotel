<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailer service
 */
class Mailer implements \SplObserver
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->mailer = $container->get('mailer');
        $this->params = $container->getParameter('mbh.mailer');
        $this->dm = $this->container->get('doctrine_mongodb');
    }

    /**
     * @param \SplSubject $notifier
     */
    public function update(\SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        $message = $notifier->getMessage();

        if ($message->getEmail()) {
            $this->send($message->getRecipients(), array_merge([
                'text' => $message->getText(),
                'type' => $message->getType(),
                'category' => $message->getCategory(),
                'subject' => $message->getSubject(),
                'hotel' => $message->getHotel(),
                'link' => $message->getLink(),
                'linkText' => $message->getLinkText(),
                'order' => $message->getOrder(),
                'signature' => $message->getSignature()
            ], $message->getAdditionalData()) , $message->getTemplate());
        }
    }

    /**
     * @param array $recipients
     * @param array $data
     * @param null $template
     * @return bool
     * @throws \Exception
     */
    public function send(array $recipients, array $data, $template = null)
    {
        if (empty($recipients)) {

            $error = 'Не удалось отправить письмо. Нет ни одного получателя.';

            if (empty($data['category'])) {
                throw new \Exception($error);
            }

            $users = $this->dm->getRepository('MBHUserBundle:User')->findBy(
                [$data['category'] . 's' => true, 'enabled' => true, 'locked' => false]
            );

            if (!count($users)) {
                throw new \Exception($error);
            }

            $recipients = [];
            foreach ($users as $user) {
                $recipients[] = [$user->getEmail() => $user->getFullName()];
            }
        }

        (empty($data['subject'])) ? $data['subject'] = $this->params['subject']: $data['subject'];
        $body = $this->twig->render(
            empty($template) ? $this->params['template'] : $template, $data
        );

        $message = \Swift_Message::newInstance();
        $message->setSubject($data['subject'])
            ->setFrom($this->params['from'])
            ->setBody($body, 'text/html')
        ;

        foreach ($recipients as $recipient) {
            $message->setTo($recipient);
            $this->mailer->send($message);
        }

        return true;
    }
}
