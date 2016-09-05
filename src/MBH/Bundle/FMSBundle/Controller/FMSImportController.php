<?php

namespace MBH\Bundle\FMSBundle\Controller;

use MBH\Bundle\FMSBundle\Form\ImportFMSType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class FMSImportController extends Controller
{
    /**
     * @param Request $request
     * @Route("/import", name="fms_import")
     * @Template("MBHFMSBundle:import:index.html.twig")
     * @return array
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

        return  ['form' => $form->createView()];
    }
}
