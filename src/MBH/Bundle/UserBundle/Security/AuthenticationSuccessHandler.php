<?php

namespace MBH\Bundle\UserBundle\Security;

use MBH\Bundle\ClientBundle\Service\Mbhs;
use MBH\Bundle\ClientBundle\Service\Dashboard\Dashboard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Custom AuthenticationSuccessHandler
 */
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    /**
     * @var Mbhs
     */
    protected $mbhs;

    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * {@inheritdoc}
     */
    public function __construct(HttpUtils $httpUtils, array $options, Mbhs $mbhs, Dashboard $dashboard)
    {
        $this->mbhs = $mbhs;
        $this->dashboard = $dashboard;
        parent::__construct($httpUtils, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $request->getSession()->set('mbh.justLogin', true);
        $this->dashboard->notify();
        $this->mbhs->login($request->getClientIp());

        return parent::onAuthenticationSuccess($request, $token);
    }
}
