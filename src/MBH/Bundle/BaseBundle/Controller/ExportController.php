<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExportController
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 * @Route("/export")
 */
class ExportController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_ADMIN')")
     * @Route("/csv/{repositoryName}", name="export_csv")
     */
    public function csvAction($repositoryName)
    {
        /** @var DocumentRepository $repository */
        $repository = $this->dm->getRepository($repositoryName);
        if(!$repository) {
            throw $this->createNotFoundException();
        }

        $this->get('database_connection')->


        $text = '';
        $response = new Response($text);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;
    }
}