<?php


namespace MBH\Bundle\BillingBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Answer;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param Client $client
     * @ParamConverter()
     * @return Response
     */
    public function installAction(Client $client = null)
    {
        $client = new Client();
        $client->setName('zalexandr');
        $installer = $this->container->get('mbh.billing.http_client_installer');
        if ($client) {
            $answer = $installer->install($client);
        } else {
            $answer = new Answer();
            $answer->setError('No user in request');
        }

        return new JsonResponse($installer->toJson($answer), 200, [], true);
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
        $client = new  Client();
        $client->setName('zalexandr');
        $installer = $this->container->get('mbh.billing.http_client_installer');
        if ($client) {
            $answer = $installer->remove($client, true);
        } else {
            $answer = new Answer();
            $answer->setError('No client '.$client.' found');
        }
        return new JsonResponse($installer->toJson($answer), 200, [], true);
    }
}