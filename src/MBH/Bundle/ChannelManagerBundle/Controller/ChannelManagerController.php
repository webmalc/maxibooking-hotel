<?php

namespace MBH\Bundle\ChannelManagerBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
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
            return  $this->get('mbh.channelmanager')->pushResponse($name, $request);
        }

        throw $this->createNotFoundException();
    }

    /**
     * Show log file
     *
     * @Route("/logs", name="channel_manager_logs")
     * @Method({"GET", "POST"})
     * @return Response
     * @Template()
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function logsAction(Request $request)
    {
        $content = null;

        $root = $this->get('kernel')->getRootDir();
        $env = $this->get('kernel')->getEnvironment();
        $file = $root.'/../var/logs/'.$env.'.channelmanager.log';
        if (file_exists($file) && is_readable($file)) {
            if ($request->getMethod() == 'POST') {
                file_put_contents($file, '');

                $request->getSession()->getFlashBag()
                    ->set(
                        'success',
                        $this->get('translator')->trans('Логи успешно очищены.')
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
                htmlentities(implode("\n", array_reverse(explode("\n", $content))))
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
            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.channelManagerController.sync_end'));

            return $this->redirect($request->get('url'));
        }

        return new Response('OK');
    }
}
