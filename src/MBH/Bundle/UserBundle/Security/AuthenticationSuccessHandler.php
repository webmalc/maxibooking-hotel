<?php

namespace MBH\Bundle\UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use MBH\Bundle\ClientBundle\Service\Mbhs;

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
     * {@inheritdoc}
     */
    public function __construct(HttpUtils $httpUtils, array $options, Mbhs $mbhs)
    {
        $this->mbhs = $mbhs;
        parent::__construct($httpUtils, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $this->mbhs->login($request->getClientIp());
        return parent::onAuthenticationSuccess($request, $token);
    }
}