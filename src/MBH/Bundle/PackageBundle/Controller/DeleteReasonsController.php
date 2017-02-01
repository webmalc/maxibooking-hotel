<?php namespace MBH\Bundle\PackageBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\HotelBundle\Controller\CheckHotelControllerInterface;
use MBH\Bundle\PackageBundle\Document\DeleteReason;
use MBH\Bundle\PackageBundle\Form\DeleteReasonsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("management/delete_reasons")
 */
class DeleteReasonsController extends Controller implements CheckHotelControllerInterface
{
    /**
     * Delete Reason action
     *
     * @Route("/", name="package_delete_reasons")
     * @Method({"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $reason = new DeleteReason();
        $form = $this->createForm(DeleteReasonsType::class, $reason, []);

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $deleteReasons = $dm->getRepository('MBHPackageBundle:DeleteReason')->findAll();

        if($request->isMethod('POST')) {
            $form->handleRequest($request);
            $dm->persist($reason);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.sourceController.record_created_success'))
            ;

            return $this->redirect($this->generateUrl('package_delete_reasons'));
        }

        return [
            'form' => $form->createView(),
            'reasons' => $deleteReasons,
            'logs' => $this->logs($reason)
        ];
    }

    /**
     * @Route("/{id}/edit", name="package_delete_reasons_edit")
     * @Method("GET")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function editDeleteReasonsAction($id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $reason = $dm->getRepository('MBHPackageBundle:DeleteReason')->find($id);

        if (!$reason) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DeleteReasonsType::class, $reason, []);

        return [
            'reason' => $reason,
            'form' => $form->createView(),
            'logs' => $this->logs($reason)
        ];
    }

    /**
     * Edits an existing reasons.
     *
     * @Route("/delete_reasons/{id}", name="reason_update")
     * @Method("POST")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template("MBHPackageBundle:DeleteReasons:index.html.twig")
     *
     */
    public function updateDeleteReasonsAction(Request $request, $id)
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $reason = $dm->getRepository('MBHPackageBundle:DeleteReason')->find($id);

        if (!$reason) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(DeleteReasonsType::class, $reason);

        $form->handleRequest($request);

        if ($form->isValid()) {

            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($reason);
            $dm->flush();

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.sourceController.record_edited_success'))
            ;
            return $this->afterSaveRedirect('package_delete_reasons', $reason->getId());
        }

        return array(
            'reasons' => $reason,
            'form' => $form->createView(),
            'logs' => $this->logs($reason)
        );
    }

    /**
     * Delete reason
     *
     * @Route("/{id}/delete", name="delete_reasons_delete")
     * @Method("GET")
     * @ParamConverter(class="MBHPackageBundle:DeleteReasons")
     * @param DeleteReason $reasons
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteReasonAction(DeleteReason $reasons)
    {
        if (!$reasons->getSystem()) {
            return $this->deleteEntity($reasons->getId(), 'MBHPackageBundle:DeleteReason', 'package_delete_reasons');
        }

    }
}