<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\ChannelManagerBundle\Lib\ChannelManagerConfigInterface;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            passthru('tail -2000 ' . escapeshellarg($file));
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
     * @throws \Throwable
     * @throws \RuntimeException
     */
    public function wizardInfoAction(string $channelManagerName, Request $request)
    {
        $channelManagerService = $this->get('mbh.channelmanager');
        $channelManagerService->checkForCMExistence($channelManagerName, true);

        $wizardManager = $this->get('mbh.cm_wizard_manager');
        $hasForm = $wizardManager->isConfiguredByTechSupport($channelManagerName);
        $channelManagerHumanName = $channelManagerService->getServiceHumanName($channelManagerName);

        $configName = $channelManagerService->getConfigFullName($channelManagerName);
        /** @var ChannelManagerConfigInterface|Base $config */
        $config = $this->dm->getRepository($configName)->findOneBy(['hotel' => $this->hotel]);

        $responseParams = [
            'hasForm' => $hasForm,
            'channelManagerName' => $channelManagerName,
            'channelManagerHumanName' => $channelManagerHumanName,
            'config' => $config
        ];

        if ($hasForm) {
            if (is_null($config)) {
                $config = (new $configName());
                $config->setHotel($this->hotel);
                $responseParams['config'] = $config;
            }

            $form = $this->createForm($wizardManager->getIntroForm($channelManagerName), $config, [
                'data_class' => $configName,
                'hotelAddress' => $wizardManager->getChannelManagerHotelAddress($this->hotel),
                'hotelAddressFormRoute' => $this->generateUrl('hotel_contact_information', ['id' => $this->hotel->getId()]),
                'hotelName' => $this->hotel->getName(),
                'hotelNameFormRoute' => $this->generateUrl('hotel_edit', ['id' => $this->hotel->getId()]),
            ]);

            if ($request->isMethod('POST') && empty($config->getId())) {
                if (!empty($config->getId())) {
                    throw new \RuntimeException($configName . ' for hotel with ID="' . $this->hotel->getId() . ' is already exists');
                }

                $form->handleRequest($request);
                $this->dm->persist($config);
                $this->dm->flush();
                $this->addFlash('success', 'channel_manager.confirmation_flash.success');

                $this->get('mbh.messages_store')
                    ->sendCMConnectionDataMessage($config, $channelManagerHumanName, $this->get('mbh.notifier.mailer'));
            }

            $responseParams = array_merge($responseParams, [
                'form' => $form->createView(),
                'messages' => [
                    'errors' => $wizardManager->getUnfilledDataErrors($this->hotel, $channelManagerName),
                ]
            ]);
        }

        return $responseParams;
    }

    /**
     * @Template()
     * @Route("/{channelManagerName}/data_warnings", name="cm_data_warnings")
     * @param string $channelManagerName
     * @return array
     * @throws \Exception
     */
    public function dataWarningsAction(string $channelManagerName)
    {
        $cmWizard = $this->get('mbh.cm_wizard_manager');
        $warningsCompiler = $this->get('mbh.warnings_compiler');
        $config = $this->get('mbh.channelmanager')->getConfigForHotel($this->hotel, $channelManagerName);

        return [
            'channelManagerName' => $channelManagerName,
            'config' => $config,
            'lastDefinedPriceCaches' =>
                $cmWizard->getLastCachesData($config, PriceCache::class),
            'lastDefinedRoomCaches' =>
                $cmWizard->getLastCachesData($config, RoomCache::class),
            'emptyRoomCachePeriods' => $warningsCompiler->getEmptyRoomCachePeriods($this->hotel),
            'emptyPriceCachePeriods' => $warningsCompiler->getEmptyPriceCachePeriods($this->hotel)
        ];
    }

    /**
     * @Route("/read_connection_instruction/{channelManagerName}", name="read_connection_instruction")
     * @param string $channelManagerName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Throwable
     */
    public function setIsConnectionInstructionRead(string $channelManagerName)
    {
        $this->get('mbh.channelmanager')->setIsConnectionInstructionRead($this->hotel, $channelManagerName);

        return $this->redirectToRoute($channelManagerName);
    }

    /**
     * @Route("/confirm_with_warnings/{channelManagerName}", name="cm_confirm_with_warnings")
     * @param string $channelManagerName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function setConfirmedWithWarningsAction(string $channelManagerName)
    {
        $channelManagerService = $this->get('mbh.channelmanager');
        $config = $channelManagerService->getConfigForHotel($this->hotel, $channelManagerName);
        $config->setIsConfirmedWithDataWarnings(true);
        $this->dm->flush();

        $this->addFlash('success', $this->get('translator')->trans('cm_wizard.configuration.success', [
            '%channelManagerHumanName%' => $channelManagerService->getServiceHumanName($channelManagerName)
        ]));

        $redirectRouteName = isset($channelManagerService::PULL_OLD_ORDERS_ROUTES[$channelManagerName])
            ? $channelManagerService::PULL_OLD_ORDERS_ROUTES[$channelManagerName]
            : $channelManagerName;

        return $this->redirectToRoute($redirectRouteName);
    }
}