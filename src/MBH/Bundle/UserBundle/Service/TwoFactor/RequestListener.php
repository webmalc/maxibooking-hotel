<?php
namespace MBH\Bundle\UserBundle\Service\TwoFactor;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RequestListener
{
    /**
     * @var HelperInterface $helper
     */
    protected $helper;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
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
     * @var bool $helper
     */
    private $enabled = false;

    /**
     * @var string
     */
    private $template;

    /**
     * Construct the listener
     * @param HelperInterface $helper
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     * @param bool $enabled
     */
    public function __construct(
        HelperInterface $helper, SecurityContextInterface $securityContext,
        EngineInterface $templating, Router $router, bool $enabled = false, string $template)
    {
        $this->helper = $helper;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->router = $router;
        $this->enabled = $enabled;
        $this->template = $template;
    }

    /**
     * Listen for request events
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $token = $this->securityContext->getToken();
        if (!$token) {
            return;
        }
        if (!$token instanceof UsernamePasswordToken) {
            return;
        }

        $key = $this->helper->getSessionKey($this->securityContext->getToken());
        $request = $event->getRequest();
        $session = $event->getRequest()->getSession();
        $user = $this->securityContext->getToken()->getUser();

        //Check if user has to do two-factor authentication
        if (!$session->has($key)) {
            return;
        }
        if ($session->get($key) === true) {
            return;
        }


        if ($request->getMethod() == 'POST') {
            //Check the authentication code
            if ($this->helper->checkCode($user, $request->get('_auth_code')) == true) {
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
            } else {
                $session->getFlashBag()->set("error", "security.two_factor.invalid_code");
            }
        }

        //Force authentication code dialog
        $response = $this->templating->renderResponse($this->template);
        $event->setResponse($response);
    }
}