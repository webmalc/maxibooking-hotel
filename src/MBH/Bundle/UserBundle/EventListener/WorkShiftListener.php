<?php

namespace MBH\Bundle\UserBundle\EventListener;

use FOS\UserBundle\Controller\SecurityController;
use MBH\Bundle\UserBundle\Controller\WorkShiftController;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Document\WorkShift;
use MBH\Bundle\UserBundle\Document\WorkShiftRepository;
use Symfony\Bundle\AsseticBundle\Controller\AsseticController;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController;
use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\Translator;


/**
 * Class WorkShiftListener
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class WorkShiftListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var WorkShiftRepository
     */
    protected $repository;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(TokenStorageInterface $tokenStorage, Session $session, Router $router, WorkShiftRepository $repository, Translator $translator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
        $this->router = $router;
        $this->repository = $repository;
        $this->translator = $translator;
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
            ExceptionController::class,
            \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController::class,
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
     * @return User|null
     */
    private function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if($token && $token->getUser() instanceof User) {
            return $token->getUser();
        }
        return null;
    }

    /**
     * @param FilterControllerEvent $event
     *
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->getUser()  && $this->getUser()->getIsEnabledWorkShift()) {
            $workShift = $this->repository->findCurrentByUser($this->getUser());
            $redirectUrl = null;

            if($workShift) {
                if($workShift->getStatus() == WorkShift::STATUS_LOCKED){
                    $redirectUrl = $this->router->generate('work_shift_wait');
                }
            } else {
                $redirectUrl = $this->router->generate('work_shift');
            }

            if($redirectUrl && !$this->isExceptController($event->getController()[0])) {
                $this->session->getFlashBag()->set('info', null);
                $event->setController(function() use ($redirectUrl) {
                    return new RedirectResponse($redirectUrl);
                });
            } elseif($workShift && $workShift->getStatus() == WorkShift::STATUS_OPEN && $workShift->getPastHours() > 12) {
                $message = $this->translator->trans('workShift.notification.work_shift_expired');
                $this->session->getFlashBag()->set('info', $message);
            }
        }
    }
}
