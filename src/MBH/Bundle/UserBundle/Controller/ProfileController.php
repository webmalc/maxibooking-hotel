<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Service;
use MBH\Bundle\UserBundle\Form\ClientContactsType;
use MBH\Bundle\UserBundle\Form\ClientServiceType;
use MBH\Bundle\UserBundle\Form\PayerType;
use MBH\Bundle\UserBundle\Form\ProfileType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * User profile controller.
 * @Route("user")
 */
class ProfileController extends Controller
{
    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER_PROFILE')")
     * @Template()
     */
    public function profileAction()
    {
        $form = $this->createForm(ProfileType::class, $this->getUser());

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Profile form
     *
     * @Route("/profile", name="user_profile_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER_PROFILE')")
     * @Template("MBHUserBundle:Profile:profile.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function profileUpdateAction(Request $request)
    {
        $entity = $this->getUser();

        $form = $this->createForm(ProfileType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->container->get('fos_user.user_manager')->updateUser($entity);

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.profileController.new_password_saved_success'));
            return $this->redirect($this->generateUrl('user_profile'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template()
     * @Route("/contacts", name="user_contacts")
     * @Security("is_granted('ROLE_MB_ACCOUNT')")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws Exception
     */
    public function contactsAction(Request $request)
    {
        $client = $this->get('mbh.client_manager')->getClient();
        $form = $this->createForm(ClientContactsType::class, $client);

        $clientEmail = $client->getEmail();
        $clientName = $client->getName();
        $clientPhone = $client->getPhone();

        $form->handleRequest($request);

        if ($clientEmail !== $client->getEmail()) {
            throw new Exception('Changed e-mail field value which can not be changed');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $clientUpdateResult = $this->get('mbh.client_manager')->updateClient($client);
            if ($clientUpdateResult->isSuccessful()) {
                if ($client->getPhone() !== $clientPhone) {
                    $this->addFlash('success',
                        $this->get('translator')->trans('controller.profileController.phone_successful_updated', [
                            '%newPhoneNumber%' => $client->getPhone()
                        ]));
                }
                if ($clientName !== $client->getName()) {
                    $this->addFlash('success', 'controller.profileController.client_successful_updated');
                }
            } else {
                if (!empty($clientUpdateResult->getErrors())) {
                    foreach ($clientUpdateResult->getErrors() as $fieldName => $errorMessages) {
                        foreach ($errorMessages as $errorMessage) {
                            if ($form->has($fieldName)) {
                                $form->get($fieldName)->addError(new FormError($errorMessage));
                            } else {
                                $form->addError(new FormError($errorMessage));
                            }
                        }
                    }
                } else {
                    $this->addBillingErrorFlash();
                }
            }

            $saveCloseString = $request->request->get('save_close');
            if (isset($saveCloseString)) {
                return $this->redirectToRoute('package');
            }
        }

        return [
            'form' => $form->createView(),
            'client' => $client
        ];
    }

    /**
     * @Template()
     * @Route("/services", name="user_services")
     */
    public function servicesAction()
    {
        $client = $this->get('mbh.client_manager')->getClient();

        $requestResult = $this->get('mbh.billing.api')->getClientServices($client);
        if (!$requestResult->isSuccessful()) {
            $services = [];
            $this->addBillingErrorFlash();
        } else {
            $services = $requestResult->getData();
        }

        $availableServicesResult = $this->get('mbh.client_manager')->getAvailableServices();
        $availableServices = $availableServicesResult->isSuccessful() ? $availableServicesResult->getData() : [];

        return [
            'services' => $services,
            'availableServices' => $availableServices
        ];
    }

    /**
     * @Template()
     * @Route("/services/add", name="add_client_service")
     */
    public function addClientServiceAction(Request $request)
    {
        $clientManager = $this->get('mbh.client_manager');
        $availableServicesResult = $clientManager->getAvailableServices();
        if (!$availableServicesResult->isSuccessful()) {
            $this->addBillingErrorFlash();

            return $this->redirectToRoute('user_services');
        }

        $availableServices = $availableServicesResult->getData();
        $form = $this->createForm(ClientServiceType::class, null, [
            'services' => $availableServices
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $res = $this->get('mbh.billing.api')->createClientService($form->getData(), $clientManager->getClient());
            dump($res);
            exit();
        }

        return [
            'form' => $form->createView(),
            'serializedServices' => $this->get('serializer')->serialize($availableServices, 'json')
        ];
    }

    /**
     * @Template()
     * @Route("/payer", name="user_payer")
     * @param Request $request
     * @return array
     */
    public function payerAction(Request $request)
    {
        //TODO: Функционал пока не реализован
        $client = $this->get('mbh.client_manager')->getClient();
        $form = $this->createForm(PayerType::class);

        return [
            'form' => $form->createView()
        ];
    }

    private function addBillingErrorFlash()
    {
        $this->addFlash('error',
            $this->get('translator')->trans('controller.profileController.request_error', [
                '%supportEmail%' => $this->getParameter('support')['email']
            ]));
    }
}
