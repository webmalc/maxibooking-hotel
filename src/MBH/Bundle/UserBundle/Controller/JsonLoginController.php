<?php

namespace MBH\Bundle\UserBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\BillingBundle\Lib\Model\Result;
use MBH\Bundle\UserBundle\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class JsonLoginController extends BaseController
{
    /**
     * @Route("/user/json_login", name="json_login", requirements={"_format":"json"})
     * @param Request $request
     * @return JsonResponse
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    public function loginAction(Request $request)
    {
        $result = new Result();
        $this->addAccessControlAllowOriginHeaders($this->getParameter('api_domains'));
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, *');

        $requestContent = json_decode($request->getContent(), true);

        if (empty($requestContent) || !isset($requestContent['username']) || !isset($requestContent['password'])) {
            $result->setErrors(['Incorrect request content']);
        } else {
            $username = $requestContent['username'];
            $password = $requestContent['password'];

            /** @var User $user */
            $user = $this->dm
                ->getRepository(User::class)
                ->findOneBy(['username' => $username]);

            if (is_null($user)) {
                $result->setErrors(['Access denied']);
            } else {
                $encoder = $this
                    ->get('security.encoder_factory')
                    ->getEncoder($user);

                if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                    $result->setErrors(['Access denied']);
                } else {
                    $token = bin2hex(random_bytes(64)) . time();
                    $user->setApiToken($token, new \DateTime($this->getParameter('token_lifetime_string')));
                    $this->dm->flush();

                    $result->setData([
                        'name' => $user->getUsername(),
                        'token' => $token
                    ]);
                }
            }
        }

        return new JsonResponse($result->getApiResponse());
    }
}