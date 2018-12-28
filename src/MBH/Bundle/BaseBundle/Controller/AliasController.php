<?php


namespace MBH\Bundle\BaseBundle\Controller;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasChecker;
use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasCheckerException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AliasController
 * @package MBH\Bundle\BaseBundle\Controller
 * @Route("/alias")
 */
class AliasController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/invalidate")
     * @Method({"POST"})
     * @Security("is_granted('ROLE_API_ADMIN')")
     */
    public function invalidateAction(Request $request): JsonResponse
    {
        $body = $request->getContent();
        $data = json_decode($body, true);
        try {
            if (null === $alias = $data['client_login'] ?? null) {
                throw new AliasCheckerException('Alias MUST be specified.');
            }

            $connectionString = 'http://%s?client=%s&action=%s';
            $clientName = strtolower($alias);
            $requestString = sprintf($connectionString, $request->server->get(AliasChecker::CHECKER_WEB_HOST), $clientName, 'invalidate');

            $client = new Client();
            $response = $client->get($requestString);
            $result = trim($response->getBody()->getContents());

            if ('error' === $result) {
                throw new AliasCheckerException('Error when alias update');
            }
            $status = 'ok';
            $message = 'Alias ' . $alias . ' was updated successful.';
        } catch (ConnectException|AliasCheckerException|\InvalidArgumentException $e) {
            $status = 'error';
            $message = $e->getMessage();
        }


        $data = [
            'status' => $status,
            'message' => $message
        ];

        $logger = $this->get('logger');
        $logger->info('Invalidate Action was done.', $data);

        return new JsonResponse($data);
    }
}