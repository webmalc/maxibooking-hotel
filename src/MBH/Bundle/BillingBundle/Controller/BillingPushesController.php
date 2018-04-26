<?php

namespace MBH\Bundle\BillingBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class BillingPushesController extends BaseController
{
    /**
     * @Method("POST")
     * @Route("/invalidate_billing_cache")
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function invalidateCacheAction(Request $request)
    {
        $this->checkToken($request->get('token'));
        $this->get('mbh.client_config_manager')->changeCacheValidity(false);

        return new JsonResponse((new Result())->getApiResponse());
    }

    /**
     * @param string|null $token
     */
    private function checkToken(string $token = null)
    {
        if ($token !== $this->getParameter('billing_front_token')) {
            throw new UnauthorizedHttpException('Incorrect token!');
        }
    }
}
