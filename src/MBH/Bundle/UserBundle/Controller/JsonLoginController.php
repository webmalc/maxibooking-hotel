<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
        // TODO: Здесь нужно добавлять Access-control header
        $requestContent = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->dm
            ->getRepository(User::class)
            ->findOneBy(['username' => $requestContent['username']]);
        $token = bin2hex(random_bytes(64)) . time();
        $user->setApiToken($token, new \DateTime('+ 2 hour'));
        $this->dm->flush();

        return new JsonResponse(['status' => 'ok', 'name' => $user->getUsername(), 'token' => $token]);
    }
}