<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Form\OrderSearchType;
use MBH\Bundle\OnlineBundle\Lib\SearchForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiPaymentFormController
 * @package MBH\Bundle\OnlineBundle\Controller
 * @Route("/api_payment_form")
 */
class ApiPaymentFormController extends Controller
{

    /**
     * @Route("/load.{_format}/{configId}", defaults={"_format" = "js"} ,name="online_payment_form_load_js")
     * @Template()
     */
    public function loadAction($configId)
    {
        $config = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($configId);

        return [
            'config' => $config,
            'wrapperId' => PaymentFormConfig::WRAPPER_ID,
        ];
    }

    /**
     * @Route("/form/iframe/{formId}", name="online_payment_form_iframe", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormIframeAction($formId = null)
    {
//        $this->setLocaleByRequest();
        /** @var PaymentFormConfig $entity */
        $entity = $this->dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($formId);

        if ($entity === null || !$entity->getIsEnabled()) {
            throw $this->createNotFoundException();
        }

        $search = new SearchForm();
        $search->setUserNameVisible($entity->isFieldUserNameIsVisible());

        $form = $this->createForm(OrderSearchType::class, $search);

        return [
            'form' => $form->createView(),
            'formId' => OrderSearchType::PREFIX,
            'entityId'     => $formId,
            'entity' => $entity,
        ];
    }

    /**
     * @Route("/search", name="online_api_payment_form_search")
     * @ParamConverter(class="MBH\Bundle\OnlineBundle\Lib\SearchForm")
     * @Method("POST")
     */
    public function searchAction(Request $request)
    {
        $searchForm = new SearchForm();

        $form = $this->createForm(OrderSearchType::class,$searchForm);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $searchForm;
        }


//        return new JsonResponse(['test']);
        return $this->json(['test']);
    }

    /**
     * Online form js
     * @Route("/form/{id}", name="online_payment_form_get", defaults={"_format"="js", "id"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     * @Template()
     */
    public function getFormAction($id = null)
    {
        $this->setLocaleByRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /** @var FormConfig $formConfig */
        $formConfig = $dm->getRepository('MBHOnlineBundle:PaymentFormConfig')
            ->findOneById($id);

        if (!$formConfig || !$formConfig->getEnabled()) {
            throw $this->createNotFoundException();
        }

        $hotelsQb = $dm->getRepository('MBHHotelBundle:Hotel')
            ->createQueryBuilder()
            ->sort('fullTitle', 'asc');

        $configHotelsIds = $this->get('mbh.helper')->toIds($formConfig->getHotels());

        $hotels = [];
        /** @var Hotel $hotel */
        foreach ($hotelsQb->getQuery()->execute() as $hotel) {
            if ($configHotelsIds && !in_array($hotel->getId(), $configHotelsIds)) {
                continue;
            }

            foreach ($hotel->getTariffs() as $tariff) {
                if ($tariff->getIsOnline()) {
                    $hotels[] = $hotel;
                    break;
                }
            }
        }

        $twig = $this->get('twig');
        $context = [
            'config' => $config,
            'formConfig' => $formConfig,
            'hotels' => $hotels,
        ];
        $text = $formConfig->getFormTemplate()
            ? $twig->createTemplate($formConfig->getFormTemplate())->render($context)
            : $twig->render('MBHOnlineBundle:Api:form.html.twig', $context);

        return [
            'styles' => $this->get('templating')->render('MBHOnlineBundle:Api:form.css.twig'),
            'text' => $text,
            'isDisplayChildAges' => $formConfig->isIsDisplayChildrenAges(),
        ];
    }
}