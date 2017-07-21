<?php


namespace MBH\Bundle\BillingBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MaintenanceController
 * @package MBH\Bundle\BillingBundle\Controller
 * @Route(
 *     "/",
 *     name="deploy",
 *     host="{subdomain}.local",
 *     defaults={"subdomain"="deploy"},
 *     requirements={"subdomain"="deploy"}
 * )
 */
class MaintenanceController extends BaseController
{
    /**
     * @Route("/install")
     */
    public function installAction()
    {
        return new Response("Install!");
    }
}