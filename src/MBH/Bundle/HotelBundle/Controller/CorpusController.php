<?php

namespace MBH\Bundle\HotelBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\HotelBundle\Document\Corpus;
use MBH\Bundle\HotelBundle\Form\CorpusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CorpusController
 * @package MBH\Bundle\HotelBundle\Controller
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 * @see Corpus
 * @Route("/corpus")
 */
class CorpusController extends BaseController
{
    /**
     * @Route("/", name="corpus")
     * @Template()
     */
    public function indexAction()
    {
        $currentHotel = $this->get('mbh.hotel.selector')->getSelected();
        $entities = $currentHotel->getCorpuses();//$entities = $this->dm->getRepository('MBHHotelBundle:Corpus')->findBy(['hotel.id' => $currentHotel->getId()]);

        return [
            'entities' => $entities,
        ];
    }

    /**
     * @Route("/new", name="corpus_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $form = $this->createForm(new CorpusType($this->dm));
        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="corpus_edit")
     * @Method("GET")
     * @Template()
     * @ParamConverter("entity", class="MBHHotelBundle:Corpus")
     */
    public function editAction(Corpus $entity)
    {
        $form = $this->createForm(new CorpusType($this->dm), $entity);
        return [
            'form' => $form->createView(),
            'entity' => $entity,
            'logs' => $this->logs($entity)
        ];
    }

    /**
     * @Route("/new", name="corpus_create")
     * @Method("PUT")
     * @Template("MBHHotelBundle:Corpus:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Corpus();
        $currentHotel = $this->get('mbh.hotel.selector')->getSelected();
        $entity->setHotel($currentHotel);

        $form = $this->createForm(new CorpusType($this->dm), $entity);
        $form->submit($request);

        if($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.corpus.created_success'));

            return $this->afterSaveRedirect('corpus', $entity->getId());
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/update/{id}", name="corpus_update")
     * @Method("POST")
     * @Template("MBHHotelBundle:Corpus:edit.html.twig")
     * @ParamConverter("entity", class="MBHHotelBundle:Corpus")
     */
    public function updateAction(Corpus $entity, Request $request)
    {
        $currentHotel = $this->get('mbh.hotel.selector')->getSelected();

        $form = $this->createForm(new CorpusType($this->dm), $entity);
        $form->submit($request);

        if($form->isValid()) {
            $this->dm->persist($entity);
            $this->dm->flush();

            $request->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.corpus.updated_success'));

            return $this->afterSaveRedirect('corpus', $entity->getId());
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{id}", name="corpus_delete")
     * @ParamConverter("entity", class="MBHHotelBundle:Corpus")
     */
    public function deleteAction(Corpus $corpus)
    {
        $this->dm->remove($corpus);
        $this->dm->flush();
        return $this->redirect($this->generateUrl('corpus'));
    }
}