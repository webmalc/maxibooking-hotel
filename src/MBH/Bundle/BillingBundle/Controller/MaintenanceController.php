<?php

namespace MBH\Bundle\BillingBundle\Controller;

use http\Exception\InvalidArgumentException;
use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Document\InstallationWorkflow;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MaintenanceController
 * @package MBH\Bundle\BillingBundle\Controller
 */
class MaintenanceController extends BaseController
{
    /**
     * @Route(
     *     "/install",
     *     requirements={"_format":"json"}
     * )
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function installAction(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        $this->checkToken($requestData['token']);
        $clientLogin = $requestData['client_login'];
        if (!$clientLogin) {
            throw new InvalidArgumentException('No login in request');
        }

        $result = $this->get('mbh.client_instance_manager')->runClientInstallationCommand($clientLogin);

        return new JsonResponse($result->getApiResponse());
    }

    /**
     * @Route("/install_properties")
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function installPropertiesAction(Request $request)
    {
        $requestData = json_decode($request->getContent(), true);
        $this->checkToken($requestData['token']);
        $login = $requestData['login'];
        $result = $this->get('mbh.client_instance_manager')->installFixtures($login);
        if ($result->isSuccessful()) {
            $admin = $this->dm->getRepository('MBHUserBundle:User')->findOneBy(['username' => 'admin']);
            $result->setData([
                'token' => $admin->getApiToken()->getToken(),
                'url' => Client::compileClientUrl($this->getParameter('client'))
            ]);
        }

        return new JsonResponse($result->getApiResponse(true));
    }

    /**
     * @Route(
     *     "/delete",
     *     requirements={"_format":"json"}
     * )
     * @ParamConverter()
     * @return Response
     */
    public function deleteAction(Client $client = null)
    {
//        $installer = $this->container->get('mbh.billing.http_client_installer');
//        if ($client) {
//            $answer = $installer->remove($client, true);
//        } else {
//            $answer = new Answer();
//            $answer->setError('No client '.$client.' found');
//        }
//
//        return new JsonResponse($installer->toJson($answer), 200, [], true);
    }

    private function checkToken(string $token)
    {
        if ($token !== BillingApi::AUTH_TOKEN) {
            throw new UnauthorizedHttpException('Incorrect token!');
        }
    }

//    /**
//     * @return Response
//     * @Route("/test")
//     */
//    public function testAction()
//    {
//        $notifier = $this->get('mbh.notifier');
//        $message = $notifier::createMessage();
//        $message
//            ->setText('Alalala')
//            ->setFrom('online_form')
//            ->setSubject('mailer.order.confirm.user.subject')
//            ->setType('success')
//            ->setCategory('notification')
//            ->setAdditionalData([
//                'prependText' => 'mailer.order.confirm.user.prepend',
//                'appendText' => 'mailer.order.confirm.user.append',
//                'fromText' => 'alala'
//            ])
//            ->setHotel($this->hotel)
//            ->setTemplate('MBHBaseBundle:Mailer:order.html.twig')
//            ->setAutohide(false)
//            ->setEnd(new \DateTime('+1 minute'))
//            ->setLink('hide')
//            ->setSignature('mailer.online.user.signature')
//            ->setMessageType(NotificationType::TASK_TYPE)
//        ;
//        $notifier
//            ->setMessage($message)
//            ->notify()
//        ;
//
//        return new Response('Alla');
//        $manager = new Manager('mongodb://admin:maxibooking@mbh-mongo:27017/admin');
//        $command = new Command(['authenticate' => ['admin', 'maxibooking']]);

//        $client = new \MongoDB\Client('mongodb://admin:maxibooking@mbh-mongo:27017/admin');
//        $manager = $client->getManager();

//        $command = new Command(['copydb' => 1, 'fromdb' => 'maxibooking', 'todb' => 'mbh_maxibooking']);

//        $cursor = $manager->executeCommand('admin', $command);
//        dump($cursor->toArray());
//        dump($client->listDatabases());
//        $mongo = $this->get('mbh.billing_mongo_client');
//        $mongo->copyDatabase('maxibooking', 'mbh_test');
//        $mongo->dropDatabase('mbh_test');
//        $mongo->createDbUser('mbh_test', 'mbhtest', 'mbhtestpassword');


//        return new Response('Alala');
//    }


}