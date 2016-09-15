<?php
namespace MBH\Bundle\UserBundle\Service\TwoFactor\Email;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class RequestListener
{
    /**
     * @var Helper $helper
     */
    protected $helper;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface  $securityContext
     */
    protected $securityContext;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    protected $templating;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    protected $router;

    /**
     * Construct the listener
     * @param Helper $helper
     * @param \Symfony\Component\Security\Core\SecurityContextInterface  $securityContext
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct(Helper $helper, SecurityContextInterface $securityContext, EngineInterface $templating, Router $router)
    {
        $this->helper = $helper;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->router = $router;
    }

    /**
     * Listen for request events
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $token = $this->securityContext->getToken();
        if (!$token)
        {
            return;
        }
        if (!$token instanceof UsernamePasswordToken)
        {
            return;
        }

        $key = $this->helper->getSessionKey($this->securityContext->getToken());
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();
        $user = $this->securityContext->getToken()->getUser();

        //Check if user has to do two-factor authentication
        if (!$session->has($key))
        {
            return;
        }
        if ($session->get($key) === true)
        {
            return;
        }


        if ($request->getMethod() == 'POST')
        {
            //Check the authentication code
            if ($this->helper->checkCode($user, $request->get('_auth_code')) == true)
            {
                //Flag authentication complete
                $session->set($key, true);
                $path = $this->router->generate('_welcome', [], UrlGeneratorInterface::ABSOLUTE_URL);
                if ($session->get('_two_factor_path')) {
                    $path = $session->get('_two_factor_path');
                }

                //Redirect to user's dashboard
                $redirect = new RedirectResponse($path);
                $event->setResponse($redirect);
                return;
            }
            else
            {
                $session->getFlashBag()->set("error", "security.two_factor.invalid_code");
            }
        }

        //Force authentication code dialog
        $response = $this->templating->renderResponse('MBHUserBundle:TwoFactor:email.html.twig');
        $event->setResponse($response);
    }
}