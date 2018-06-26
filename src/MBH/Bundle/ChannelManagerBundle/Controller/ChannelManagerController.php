<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractChannelManagerService;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\UserBundle\DataFixtures\MongoDB\UserData;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/")
 */
class ChannelManagerController extends Controller
{
    /**
     * @Route("/package/notifications/{name}", name="channel_manager_notifications")
     * @Method({"POST", "GET"})
     * @param string $name
     * @return Response
     * @param Request $request
     */
    public function packageNotificationsAction(Request $request, $name)
    {
        $result = $this->get('mbh.channelmanager')->pullOrders($name);

        if ($result && !empty($result[$name]['result'])) {
            return $this->get('mbh.channelmanager')->pushResponse($name, $request);
        }

        throw $this->createNotFoundException();
    }

    /**
     * Show log file
     *
     * @Route("/logs", name="channel_manager_logs")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response|array
     * @Template()
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function logsAction(Request $request)
    {
        $content = null;

        $file = $this->container->get('mbh.channelmanager.logger_handler')->getUrl();

        if (file_exists($file) && is_readable($file)) {
            if ($request->getMethod() == 'POST') {
                file_put_contents($file, '');

                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('controller.channel_manager_controller.logs_clear_successful')
                );

                return $this->redirect($this->generateUrl('channel_manager_logs'));
            }

            ob_start();
            passthru('tail -1000 ' . escapeshellarg($file));
            $content = trim(preg_replace('/==>.*<==/', '', ob_get_clean()));
        }

        return [
            'content' => str_replace(
                PHP_EOL,
                '<br><br>',
                htmlentities(implode("\n", array_reverse(explode("\n", $content))), ENT_SUBSTITUTE, "UTF-8")
            )
        ];
    }

    /**
     * Sync rooms & tariffs
     *
     * @Route("/sync", name="channel_manager_sync")
     * @Method({"GET"})
     * @param Request $request
     * @return Response
     */
    public function syncAction(Request $request)
    {
        $cm = $this->get('mbh.channelmanager');
        $cm->clearAllConfigsInBackground();
        $cm->updateInBackground();

        if (!empty($request->get('url'))) {
            $this->addFlash('success', 'controller.channelManagerController.sync_end');

            return $this->redirect($request->get('url'));
        }

        return new Response('OK');
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/{channelManagerName}/wizard_info", name="wizard_info")
     * @param string $channelManagerName
     * @param Request $request
     * @Template()
     * @return Response
     */
    public function wizardInfoAction(string $channelManagerName, Request $request)
    {
        $channelManagerService = $this->get('mbh.channelmanager');
        $channelManagerService->checkForCMExistence($channelManagerName, true);

        $infoMessage = 'controller.channelManagerController.wizard_info_text.' . $channelManagerName;
        $wizardManager = $this->get('mbh.cm_wizard_manager');
        $hasForm = $wizardManager->isConfiguredByTechSupport($channelManagerName);

        $responseParams = [
            'infoMessage' => $infoMessage,
            'hasForm' => $hasForm,
            'channelManagerName' => $channelManagerName,
        ];

        if ($hasForm) {
            $configName = $channelManagerService->getServiceIdByName($channelManagerName)->getConfigFullName();

            /** @var ChannelManagerConfigInterface|Base $config */
            $config = $this->dm->getRepository($configName)->findOneBy(['hotel' => $this->hotel]);
            if (is_null($config)) {
                $config = (new $configName());
                $config->setHotel($this->hotel);
            }
            $form = $this->createForm($wizardManager->getIntroForm($channelManagerName), $config, [
                'data_class' => $configName
            ]);

            if ($request->isMethod('POST')) {
                if (!empty($config->getId())) {
                    throw new \RuntimeException($configName . ' for hotel with ID="' . $this->hotel->getId() . ' is already exists');
                }

                $form->handleRequest($request);
                $this->dm->persist($config);
                $this->dm->flush();
                //TODO: Дополнить
                $this->addFlash('success', 'Данные подключения отправлены в тех.поддержку.');
            }

            $responseParams = array_merge($responseParams, [
                'form' => $form->createView(),
                'config' => $config
            ]);
        }

        return $responseParams;
    }

    /**
     * @Route("/confirm_cm_config/{channelManagerName}", name="confirm_cm_config")
     * @param string $channelManagerName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmConfigReadiness(string $channelManagerName)
    {
        if (!$this->getUser() instanceof User || $this->getUser()->getUsername() !== UserData::MB_USER_USERNAME) {
            throw new AccessDeniedException('Confirm channel manager config can only mb user');
        }

        /** @var AbstractChannelManagerService $cmService */
        $cmService = $this->get('mbh.channelmanager')->getServiceIdByName($channelManagerName);
        $confirmationResult = $cmService->confirmReadinessOfCM($this->hotel, $channelManagerName);

        if ($confirmationResult) {
            $this->addFlash('success', 'channel_manager.confirmation.success');
            //TODO: Добавить отправку сообшения
        }

        return $this->redirectToRoute($channelManagerName);
    }
}