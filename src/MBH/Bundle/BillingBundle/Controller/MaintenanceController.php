<?php


namespace MBH\Bundle\BillingBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Answer;
use MBH\Bundle\BillingBundle\Lib\Model\string;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    public function installAction(string $client = null)
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
    public function deleteAction(string $client = null)
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
}