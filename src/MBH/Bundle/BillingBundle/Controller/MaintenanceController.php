<?php


namespace MBH\Bundle\BillingBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BaseBundle\Document\NotificationType;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param string $client
     * @ParamConverter()
     * @return Response
     */
    public function installAction(Client $client = null)
    {
        $application = new Application($this->container->get('kernel'));
        $application->setAutoExit(true);
        $input = new ArrayInput(
            [
                'command' => 'mbh:client:install',
                '--clients' => $client->getName(),
                '--billing'
            ]
        );
        $output = new NullOutput();
        $application->run($input, $output);

        return new JsonResponse(['status' => 'command started']);
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

    /**
     * @return Response
     * @Route("/test")
     */
    public function testAction()
    {
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
        $mongo = $this->get('mbh.billing_mongo_client');
//        $mongo->copyDatabase('maxibooking', 'mbh_test');
//        $mongo->dropDatabase('mbh_test');
        $mongo->createDbUser('mbh_test', 'mbhtest', 'mbhtestpassword');


        return new Response('Alala');
    }
}