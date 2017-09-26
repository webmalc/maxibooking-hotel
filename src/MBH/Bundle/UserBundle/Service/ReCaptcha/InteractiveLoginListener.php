<?php
namespace MBH\Bundle\UserBundle\Service\ReCaptcha;

use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
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
    protected $billing;

    /**
     * InteractiveLoginListener constructor.
     * @param array $params
     * @param Session $session
     * @param BillingApi $billing
     */
    public function __construct(array $params, Session $session, BillingApi $billing)
    {
        $this->params = $params;
        $this->session = $session;
        $this->billing = $billing;
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


        $clientData = $this->billing->getClient();
        //TODO: Здесь мы записываем в сессию количество доступных номеров
        $this->session->set(Client::AVAILABLE_ROOMS_LIMIT, $clientData[Client::AVAILABLE_ROOMS_LIMIT]);
        $clientStatus = $clientData[Client::CLIENT_STATUS_FIELD_NAME];
        if ($clientStatus != 'active') {
            
        }
    }

}