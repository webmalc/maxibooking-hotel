<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use MBH\Bundle\BillingBundle\Lib\Model\Client;
use \ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class InteractiveLoginListener
{
    /**
     * @var array
     */
    protected $params;
    protected $session;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param Session $session
     */
    public function __construct(array $params, Session $session)
    {
        $this->params = $params;
        $this->session = $session;
    }

    /**
     * Listen for successful login events
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        $reCaptcha = new ReCaptcha($this->params['secret']);
        if (!$reCaptcha->verify($request->get('g-recaptcha-response'), $request->getClientIp())->isSuccess()) {
            throw new BadCredentialsException('Captcha is invalid');
        }
        //TODO: Здесь мы записываем в сессию количество доступных номеров
        $this->session->set(Client::AVAILABLE_NUMBER_OF_ROOMS, 20);
    }

}