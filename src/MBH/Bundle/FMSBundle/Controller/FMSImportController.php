<?php

namespace MBH\Bundle\FMSBundle\Controller;

use MBH\Bundle\FMSBundle\Form\ImportFMSType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FMSImportController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     * @Route("/import", name="fms_import")
     */
    public function importPageAction(Request $request)
    {
        $form = $this->createForm(ImportFMSType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $parameters = $form->getData();
            $this->get('mbh.fms.fms_export')->sendEmail($parameters['startDate'], $parameters['endDate']);
            $this->addFlash('success', 'success_import_toFMS');
        }

        return $this->render('@MBHFMS/import/index.html.twig', ['form' => $form->createView()]);
    }
}
