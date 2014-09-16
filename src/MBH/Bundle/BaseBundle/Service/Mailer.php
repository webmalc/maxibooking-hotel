<?php

namespace MBH\Bundle\BaseBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailer service
 */
class Mailer
{
    /**
     * @array
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->mailer = $container->get('mailer');
        $this->params = $container->getParameter('mbh.mailer');
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
            throw new \Exception('Не удалось отправить письмо. Нет не одного получателя.');
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
