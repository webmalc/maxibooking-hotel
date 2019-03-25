<?php
/**
 * Date: 22.03.19
 */

namespace MBH\Bundle\OnlineBundle\Controller\SearchForm;


use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Services\DataForSearchForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SearchFormController
 * @package MBH\Bundle\OnlineBundle\Controller\SearchForm
 *
 * @Route("/api")
 */
class SearchFormController extends Controller
{
    /**
     * Old Online form iframe, for old clients
     *
     * @Route("/form/iframe/{formId}", name="online_form_iframe", defaults={"formId"=null})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function getFormIframeAction(Request $request, $formId)
    {
        if ($formId === null) {
            $formId = $request->query->get('formId');

            if ($formId === null) {
                throw  $this->createNotFoundException();
            }
        }

        $formConfig = $this->dm->getRepository(FormConfig::class)
            ->findOneById($formId);

        if (!$formConfig || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $this->setLocaleByRequest();

        $unparsedUrl = function (string $rawUrl): string {
            $parsedUrl = parse_url($rawUrl);
            $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
            $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
            $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

            return $scheme.$host.$port;
        };

        return new RedirectResponse(
            $this->generateUrl(
                FormConfig::ROUTER_NAME_LOAD_ALL_IFRAME,
                array_merge(
                    $request->query->all(),
                    [
                        'formConfigId' => $formConfig->getId(),
                        'redirectKey'  => sha1($formConfig->getCreatedAt()->getTimestamp())
                    ]
                )
            ),
            302,
            [
                'Access-Control-Allow-Origin' => $unparsedUrl($formConfig->getResultsUrl())
            ]
        );
    }

    /**
     * @Route("/file/{formConfigId}/load-search-form", name=FormConfig::ROUTER_NAME_LOAD_ALL_IFRAME, defaults={"_format"="js"})
     * @Cache(expires="tomorrow", public=true)
     */
    public function loadAllScriptForSearchAction(Request $request, $formConfigId)
    {
        /** @var FormConfig $formConfig */
        $formConfig = $this->dm->getRepository(FormConfig::class)
            ->find($formConfigId);

        if ($formConfig === null || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $this->setLocaleByRequest();

        $response = new Response();

        $redirectKey = $request->get('redirectKey') ?? null;
        if ($redirectKey !== null && $redirectKey === sha1($formConfig->getCreatedAt()->getTimestamp())) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        $dataForSearchForm = $this->get(DataForSearchForm::class)->setFormConfig($formConfig);

        return $this->render(
            '@MBHOnline/Api/search-form/script-for-load-all-iframe.js.twig',
            array_merge(
                [
                    'formConfig' => $formConfig,
                ],
                $dataForSearchForm->getConfigForLoadAllIframe()
            ),
            $response
        );
    }

    /**
     * Online form iframe calendar
     * @Route("/form/iframe/calendar/{formConfigId}", name=MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig::ROUTER_NAME_CALENDAR_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function calendarIframeAction($formConfigId)
    {
        $formConfig = $this->dm->getRepository(FormConfig::class)->findOneById($formConfigId);

        if ($formConfig === null || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $this->setLocaleByRequest();

        return $this->render(
            '@MBHOnline/Api/search-form/calendar-iframe.html.twig',
            [
                'formConfig' => $formConfig
            ]
        );
    }

    /**
     * @Route("/form/iframe/additional_form/{formConfigId}", name=MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig::ROUTER_NAME_ADDITIONAL_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function additonalFormIframeAction($formConfigId)
    {
        $this->setLocaleByRequest();

        $formConfig = $this->dm->getRepository(FormConfig::class)->findOneById($formConfigId);

        if ($formConfig === null || !$formConfig->isEnabled() || !$formConfig->isUseAdditionalForm()) {
            throw $this->createNotFoundException();
        }

        /** @var DataForSearchForm $helperDataForm */
        $helperDataForm = $this->get(DataForSearchForm::class)->setFormConfig($formConfig);

        return $this->render(
            '@MBHOnline/Api/search-form/additional-form-iframe.html.twig',
            [
                'formConfig' => $formConfig,
                'choices'    => $helperDataForm->getRoomTypes(),
            ]
        );
    }

    /**
     * Online form iframe
     * @Route("/form/search_iframe/{formConfigId}", name=FormConfig::ROUTER_NAME_SEARCH_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function searchIframeAction($formConfigId)
    {
        $formConfig = $this->dm->getRepository(FormConfig::class)
            ->findOneById($formConfigId);

        if (!$formConfig || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $this->setLocaleByRequest();

        return $this->render(
            '@MBHOnline/Api/search-form/search-iframe.html.twig',
            [
                'formConfig' => $formConfig,
                'siteConfig' => $this->get('mbh.site_manager')->getSiteConfig(),
            ]
        );
    }

    /**
     * Online form js
     * @Route("/form/{formConfigId}", name="online_form_get", defaults={"_format"="js"})
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function searchInsertHtmlFormAction($formConfigId)
    {
        $this->setLocaleByRequest();

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $config = $this->container->getParameter('mbh.online.form');
        /** @var FormConfig $formConfig */
        $formConfig = $dm->getRepository(FormConfig::class)->findOneById($formConfigId);

        if (!$formConfig || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        /** @var DataForSearchForm $dataForSearchForm */
        $dataForSearchForm = $this->get(DataForSearchForm::class)->setFormConfig($formConfig);

        $twig = $this->get('twig');
        $context = [
            'config'     => $config,
            'formConfig' => $formConfig,
            'hotels'     => $dataForSearchForm->getHotels(),
            'choices'    => $dataForSearchForm->getRoomTypes(),
        ];

        $text = $formConfig->getFormTemplate()
            ? $twig->createTemplate($formConfig->getFormTemplate())->render($context)
            : $twig->render('@MBHOnline/Api/search-form/search-html-form.html.twig', $context);

        return $this->render(
            '@MBHOnline/Api/search-form/search-insert-html-form.js.twig',
            [
                'text'               => $text,
                'isDisplayChildAges' => $formConfig->isDisplayChildrenAges(),
            ]
        );
    }
}