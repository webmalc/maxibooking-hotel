<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class JsonLoginController extends BaseController
{
    /**
     * @Route("/user/json_login", name="json_login", requirements={"_format":"json"})
     * @param Request $request
     * @return JsonResponse
     */
    public function loginAction(Request $request)
    {
        $username = $this->getUser()->getUsername();

        return new JsonResponse(['status' => 'ok', 'name' => $username]);
    }
}