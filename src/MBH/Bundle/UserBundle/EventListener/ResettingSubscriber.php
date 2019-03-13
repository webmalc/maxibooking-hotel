<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 20.07.17
 * Time: 11:30
 */

namespace MBH\Bundle\UserBundle\EventListener;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;
use MBH\Bundle\UserBundle\Service\ReCaptcha\InvisibleCaptcha;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResettingSubscriber implements EventSubscriberInterface
{
    protected $params;
    /** @var  TwigEngine */
    protected $twigEngine;
    /** @var  TranslatorInterface */
    protected $translator;
    /** @var InvisibleCaptcha */
    protected $captcha;

    /**
     * @param array $params
     * @param TwigEngine $twigEngine
     * @param TranslatorInterface $translator
     * @param InvisibleCaptcha $captcha
     */
    public function __construct(array $params, TwigEngine $twigEngine, TranslatorInterface $translator, InvisibleCaptcha $captcha)
    {
        $this->params = $params;
        $this->twigEngine = $twigEngine;
        $this->translator = $translator;
        $this->captcha = $captcha;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE => 'onResettingResetInitialize',
        );
    }

    /**
     * @param GetResponseUserEvent $event
     */
    public function onResettingResetInitialize(GetResponseUserEvent $event)
    {
        $request = $event->getRequest();
        $errors = [];

        if (!$request->get('re_token', false)) {
            $errors[] = $this->translator->trans('resetting.error.enter_captcha', [], 'FOSUserBundle');
        }
        try {
            $this->captcha->validate($request->get('re_token'));
        } catch (InvisibleCaptchaException $e) {
            $errors[] = $this->translator->trans(
                'resetting.error.user_not_exists',
                ['%username%' => $request->request->get('username')],
                'FOSUserBundle'
            );
        }


        if (is_null($event->getUser())) {
            $username = $request->request->get('username');
            $errors[] = $this->translator->trans(
                'resetting.error.user_not_exists',
                ['%username%' => $username],
                'FOSUserBundle'
            );
        }

        if (count($errors) > 0) {
            $responseContent = $this->twigEngine->renderResponse('FOSUserBundle:Resetting:request.html.twig',
                ['errors' => $errors]
            );

            $event->setResponse($responseContent);
        }
    }
}