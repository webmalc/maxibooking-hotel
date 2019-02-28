<?php

namespace MBH\Bundle\UserBundle\Security\ReCaptcha;


use MBH\Bundle\UserBundle\Service\ReCaptcha\InvisibleCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use MBH\Bundle\UserBundle\Lib\Exception\InvisibleCaptchaException;

class MBHAuthenticator implements SimpleFormAuthenticatorInterface
{

    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    /**
     * @var InvisibleCaptcha
     */
    private $captcha;

    /**
     * MBHAuthenticator constructor.
     * @param UserPasswordEncoder $encoder
     * @param InvisibleCaptcha $captcha
     */
    public function __construct(UserPasswordEncoder $encoder, InvisibleCaptcha $captcha)
    {
        $this->encoder = $encoder;
        $this->captcha = $captcha;
    }

    /**
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param $providerKey
     * @return UsernamePasswordToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $this->captcha->validate($token->getCredentials()['re_token']);
        } catch (InvisibleCaptchaException $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new BadCredentialsException('The presented password is invalid.');
        }

        $passwordValid = $this->encoder->isPasswordValid($user, $token->getCredentials()['password']);

        if (!$passwordValid) {
            throw new BadCredentialsException('The presented password is invalid.');
        }
        return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
    }

    /**
     * @param TokenInterface $token
     * @param $providerKey
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @param Request $request
     * @param $username
     * @param $password
     * @param $providerKey
     * @return UsernamePasswordToken
     */
    public function createToken(Request $request, $username, $password, $providerKey)
    {
        if (!$request->get('re_token', false)) {
            throw new BadCredentialsException('Invalid captcha');
        }
//        try {
//            $this->captcha->validate($request->get('re_token'));
//        } catch (InvisibleCaptchaException $e) {
//            throw new BadCredentialsException('The presented password is invalid.');
//        }
        return new UsernamePasswordToken(
            $username,
            ['password' => $password, 're_token' => $request->get('re_token')],
            $providerKey
        );
    }
}