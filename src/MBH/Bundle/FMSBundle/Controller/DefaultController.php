<?php

namespace MBH\Bundle\FMSBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\FMSBundle\Form\ImportFMSType;
use MBH\Bundle\FMSBundle\MBHFMSBundle;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MongoDBODMProxies\__CG__\Gedmo\Loggable\Document\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/lomai")
     */
    public function indexAction()
    {
        /*$dm = $this->get('doctrine.odm.mongodb.document_manager');

        $entries = $dm->getRepository('MBHPackageBundle:Tourist')->findBy([], ['createdAt' => 'desc'], 1);
        return $this->render('@MBHFMS/test/text.html.twig', ['entry' => $entries[0]]);*/
        //$this->get('mbh.fms.fms_export')->sendEmail();
        return $this->render('MBHFMSBundle:test:text.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     * @Route("/import", name="import")
     */
    public function importPageAction(Request $request)
    {
        $parameters = [];
        $form = $this->createForm(ImportFMSType::class, $parameters);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $parameters = $form->getData();
            $this->get('mbh.fms.fms_export')->sendEmail($parameters['startDate'], $parameters['endDate']);
            $this->addFlash('success', 'success_import_toFMS');
        }

        return $this->render('@MBHFMS/import/index.html.twig', ['form' => $form->createView()]);
    }
}
