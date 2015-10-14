<?php

namespace MBH\Bundle\UserBundle\EventListener;

use FOS\UserBundle\Controller\SecurityController;
use MBH\Bundle\UserBundle\Controller\WorkShiftController;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Document\WorkShiftRepository;
use Symfony\Bundle\AsseticBundle\Controller\AsseticController;
use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;


/**
 * Class OnController
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OnController
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getExceptControllers()
    {
        return [
            WorkShiftController::class,
            SecurityController::class,
            ProfilerController::class,
            AsseticController::class,
        ];
    }

    /**
     * @param object $controller
     * @return bool
     */
    protected function isExceptController($controller)
    {
        foreach($this->getExceptControllers() as $exceptController) {
            if ($controller instanceof $exceptController) {
                return true;
            };
        }

        return false;
    }

    /**
     * @param FilterControllerEvent $event
     *
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        $token = $this->container->get('security.token_storage')->getToken();
        //$this->container->get('security.authorization_checker')->isGranted('ROLE_BASE_USER') && //AuthenticatedVoter::IS_AUTHENTICATED_FULLY
        if ($token &&
            $token->getUser() instanceof User &&
            $token->getUser()->getIsEnabledWorkShift()) {
            $dm = $this->container->get('doctrine_mongodb');
            /** @var WorkShiftRepository $workShiftRepository */
            $workShiftRepository = $dm->getRepository('MBHUserBundle:WorkShift');
            $workShift = $workShiftRepository->findCurrent($token->getUser());
            if(!$workShift && (!$this->isExceptController($controller[0]))) {
                $route = $this->container->get('router');
                $event->setController(function() use ($route) {
                   return new RedirectResponse($route->generate('work_shift'));
                });
            }elseif($workShift && $workShift->getPastHours() > 12) {
                $message = $this->container->get('translator')->trans('workShift.notification.work_shift_expired');
                $this->container->get('session')->getFlashBag()->set('info', $message);
            }
        }
    }
}
