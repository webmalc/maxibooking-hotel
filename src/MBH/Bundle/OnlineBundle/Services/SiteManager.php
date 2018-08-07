<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\WarningsCompiler;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use MBH\Bundle\BillingBundle\Lib\Model\WebSite;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SiteManager
{
    const DEFAULT_RESULTS_PAGE = '/results/index.html';
    const PERSONAL_DATA_POLICIES_PAGE = '/personal-data-policies/index.html?q';
    const DEFAULT_BOOTSTRAP_THEME = 'cerulean';
    const SITE_DOMAIN = '.maaaxi.com';
    const SITE_PROTOCOL = 'http://';
    const MANDATORY_FIELDS_BY_ROUTE_NAMES = [
        Hotel::class => [
            'hotel_edit' => ['description', 'logoImage'],
            'hotel_contact_information' => ['contactInformation', 'latitude', 'longitude', 'street', 'settlement', 'cityId', 'house', 'zipCode'],
            'hotel_images' => ['images']
        ],
        RoomType::class => [
            'room_type_edit' => ['description', 'roomSpace', 'facilities'],
            'room_type_image_edit' => ['onlineImages']
        ]
    ];

    /** @var DocumentManager */
    private $dm;
    private $documentFieldsManager;
    private $translator;
    private $warningsCompiler;
    private $billingApi;

    public function __construct(
        DocumentManager $dm, 
        DocumentFieldsManager $documentFieldsManager, 
        TranslatorInterface $translator,
        WarningsCompiler $warningsCompiler,
        BillingApi $billingApi
    ) {
        $this->dm = $dm;
        $this->documentFieldsManager = $documentFieldsManager;
        $this->translator = $translator;
        $this->warningsCompiler = $warningsCompiler;
        $this->billingApi = $billingApi;
    }

    /**
     * @param $fieldsDataByRouteNames
     * @param $document
     * @return array
     */
    private function checkFieldsCorrectness($fieldsDataByRouteNames, $document)
    {
        $result = [];

        foreach ($fieldsDataByRouteNames as $routeName => $fieldsDataByRouteName) {
            $result[$routeName] = $this->documentFieldsManager->getFieldsByCorrectnessStatuses($fieldsDataByRouteName, $document);
        }

        return $result;
    }

    /**
     * @param SiteConfig|null $config
     * @return array
     * @throws \Exception
     */
    public function getHotelsSettingsInfo(SiteConfig $config = null)
    {
        $settingsInfo = [];
        if (!is_null($config) && $config->getIsEnabled()) {
            foreach ($config->getHotels() as $hotel) {
                $numberOfWarnings = $this->getNumberOfWarnings($hotel);
                foreach ($hotel->getRoomTypes() as $roomType) {
                    $numberOfWarnings += $this->getNumberOfWarnings($roomType);
                    if (isset($this->warningsCompiler->getEmptyRoomCachePeriods()[$hotel->getId()][$roomType->getId()])) {
                        $numberOfWarnings++;
                    }
                    if (isset($this->warningsCompiler->getEmptyPriceCachePeriods()[$hotel->getId()][$roomType->getId()])) {
                        $numberOfWarnings++;
                    }
                }

                $settingsInfo[] = [
                    'hotel' => $hotel,
                    'numberOfWarnings' => $numberOfWarnings,
                ];
            }
        }

        return $settingsInfo;
    }

    /**
     * @return FormConfig
     */
    public function fetchFormConfig()
    {
        $formConfig = $this->dm->getRepository('MBHOnlineBundle:FormConfig')->findOneBy(['forMbSite' => true]);
        if (is_null($formConfig)) {
            $formConfig = (new FormConfig())
                ->setForMbSite(true)
                ->setIsFullWidth(true)
                ->setIsHorizontal(true)
                ->setTheme(FormConfig::THEMES[self::DEFAULT_BOOTSTRAP_THEME])
                ->setResultsUrl(self::DEFAULT_RESULTS_PAGE);

            $this->dm->persist($formConfig);
        }

        return $formConfig;
    }

    /**
     * @param $document
     * @param FormInterface $form
     * @param string $routeName
     */
    public function addFormErrorsForFieldsMandatoryForSite($document, FormInterface $form, string $routeName)
    {
        $siteConfig = $this->getSiteConfig();
        if ($siteConfig && $siteConfig->getIsEnabled()) {
            $documentClass = get_class($document);
            $siteDataCorrectness = $this->getDocumentFieldsCorrectnessTypesByRoutesNames($document);
            if (isset($siteDataCorrectness['fieldsData'][$routeName]['empty'])) {
                $emptyFields = $siteDataCorrectness['fieldsData'][$routeName]['empty'];
                foreach ($emptyFields as $emptyField) {
                    $formField =
                        $this->documentFieldsManager->getFormFieldByDocumentField($documentClass, $emptyField);
                    $fieldTitle = $this->documentFieldsManager->getFieldName($documentClass, $emptyField);
                    $errorMessage = $this->translator->trans('site_manager.mandatory_field_empty.error',
                        ['%fieldTitle%' => $fieldTitle]);
                    $form->get($formField)->addError(new FormError($errorMessage));
                }
            }
        }
    }

    /**
     * @return SiteConfig|null|object
     */
    public function getSiteConfig()
    {
        return $this->dm->getRepository('MBHOnlineBundle:SiteConfig')->findOneBy([]);
    }

    /**
     * @param Hotel $hotel
     * @param Client $client
     * @return SiteConfig
     */
    public function createOrUpdateForHotel(Hotel $hotel, Client $client)
    {
        $config = $this->getSiteConfig();
        if (is_null($config)) {
            $config = new SiteConfig();
            $this->dm->persist($config);
            $config->addHotel($hotel);

            $siteDomain = '';
            if ($this->checkSiteDomain($client->getLogin())) {
                $siteDomain = $client->getLogin();
            } elseif ($client->getCity()) {
                $domainWithCityName = $client->getLogin() . '-' . $this->billingApi->getCityById($client->getCity(), 'en')->getName();
                if ($this->checkSiteDomain($domainWithCityName)) {
                    $siteDomain = $domainWithCityName;
                }
            }

            if (empty($siteDomain)) {
                $siteDomain = $client->getLogin() . rand(0, 1000000);
            }

            $config->setSiteDomain($siteDomain);
            $clientSite = (new WebSite())
                ->setUrl($this->compileSiteAddress($siteDomain))
                ->setClient($client->getLogin());
            $this->billingApi->addClientSite($clientSite);
        }

        $this->updateSiteFormConfig($config, $this->fetchFormConfig(), []);

        return $config;
    }

    /**
     * @param string $siteDomain
     * @return bool
     */
    public function checkSiteDomain(string $siteDomain)
    {
        $sitesResult = $this->billingApi->getSitesByUrlResult($this->compileSiteAddress($siteDomain));

        return $sitesResult->isSuccessful() && empty($sitesResult->getData());
    }

    /**
     * @param $document
     * @return array
     */
    public function getDocumentFieldsCorrectnessTypesByRoutesNames($document): array
    {
        $documentClass = ClassUtils::getClass($document);
        $fieldsDataByRouteNames = self::MANDATORY_FIELDS_BY_ROUTE_NAMES[$documentClass];

        return [
            'document' => $document,
            'fieldsData' => $this->checkFieldsCorrectness($fieldsDataByRouteNames, $document)
        ];
    }

    /**
     * @param $document
     * @return integer
     */
    private function getNumberOfWarnings($document)
    {
        $fieldsCorrectnessByRoutes = $this->getDocumentFieldsCorrectnessTypesByRoutesNames($document);

        return array_reduce($fieldsCorrectnessByRoutes['fieldsData'], function ($result, $fieldsByCorrectnessType) {
            foreach ($fieldsByCorrectnessType as $correctnessType => $fields) {
                if ($correctnessType !== 'correct') {
                    $result += count($fields);
                }
            }

            return $result;
        });
    }

    /**
     * @param SiteConfig $config
     * @param FormConfig $formConfig
     * @param array $paymentTypes
     */
    public function updateSiteFormConfig(SiteConfig $config, FormConfig $formConfig, array $paymentTypes)
    {
        $siteAddress = $this->compileSiteAddress($config->getSiteDomain());

        $roomTypes = [];
        foreach ($config->getHotels() as $hotel) {
            $roomTypes = array_merge($roomTypes, $hotel->getRoomTypes()->toArray());
        }

        $formConfig
            ->setResultsUrl($siteAddress)
            ->setHotels($config->getHotels()->toArray())
            ->setRoomTypeChoices($roomTypes)
            ->setPaymentTypes($paymentTypes);

        if (!empty($config->getPersonalDataPolicies())) {
            $formConfig->setPersonalDataPolicies($siteAddress . SiteManager::PERSONAL_DATA_POLICIES_PAGE);
        }
    }

    /**
     * @return string
     */
    public function getSiteAddress()
    {
        return $this->getSiteConfig() && $this->getSiteConfig()->getSiteDomain()
            ? $this->compileSiteAddress($this->getSiteConfig()->getSiteDomain())
            : null;
    }

    /**
     * @param string $siteDomain
     * @return string
     */
    public function compileSiteAddress(string $siteDomain)
    {
        return self::SITE_PROTOCOL . $siteDomain . self::SITE_DOMAIN;
    }
}