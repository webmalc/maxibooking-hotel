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
     * Online form iframe calendar
     * @Route("/form/iframe/calendar", name=MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig::ROUTER_NAME_CALENDAR_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function getCalendarIframeAction()
    {
        $this->setLocaleByRequest();

        return $this->render('@MBHOnline/Api/search-form/calendar-iframe.html.twig');
    }

    /**
     * @Route("/form/iframe/additional_data/{formConfigId}", name=MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig::ROUTER_NAME_ADDITIONAL_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function getAdditonalFormIframeAction($formConfigId)
    {
        $this->setLocaleByRequest();

        $formConfig = $this->dm->getRepository(FormConfig::class)->findOneById($formConfigId);

        if ($formConfig === null || !$formConfig->isEnabled()) {
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
     * @Route("/form/iframe/{formConfigId}", name=FormConfig::ROUTER_NAME_SEARCH_IFRAME)
     * @Method("GET")
     * @Cache(expires="tomorrow", public=true)
     */
    public function searchIframeAction(Request $request, $formConfigId = null)
    {
        if ($formConfigId === null) {
            $formConfigId = $request->get('formId');
        }

        $this->setLocaleByRequest();
        $formConfig = $this->dm->getRepository(FormConfig::class)
            ->findOneById($formConfigId);

        if ($request->get('redirect')) {
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

    /**
     * @Route("/file/{formConfigId}/load-search-form", name=FormConfig::ROUTER_NAME_LOAD_ALL_IFRAME, defaults={"_format"="js"})
     * @Cache(expires="tomorrow", public=true)
     */
    public function loadAllScriptForSearchAction(Request $request, $formConfigId)
    {
        $this->setLocaleByRequest();
        /** @var FormConfig $formConfig */
        $formConfig = $this->dm->getRepository(FormConfig::class)
            ->find($formConfigId);

        if ($formConfig === null || !$formConfig->isEnabled()) {
            throw $this->createNotFoundException();
        }

        $dataForSearchForm = $this->get(DataForSearchForm::class)->setFormConfig($formConfig);
        $response = new Response();

        $redirectKey = $request->get('redirectKey') ?? null;
        if ($redirectKey !== null && $redirectKey === sha1($formConfig->getCreatedAt()->getTimestamp())) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        return $this->render(
            '@MBHOnline/Api/search-form/script-for-load-all-iframe.js.twig',
            [
                'formConfig' => $formConfig,
                'urls'       => $dataForSearchForm->getAllUrlIframe(),
            ],
            $response
        );
    }
}