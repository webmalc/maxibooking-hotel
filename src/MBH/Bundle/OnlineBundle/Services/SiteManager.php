<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\BaseBundle\Service\WarningsCompiler;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\SettingsOnlineForm\FormConfig;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SiteManager
{
    const DEFAULT_RESULTS_PAGE = '/results';
    const PERSONAL_DATA_POLICIES_PAGE = '/personal-data-policies?q';
    const DEFAULT_BOOTSTRAP_THEME = 'cerulean';
    const MANDATORY_FIELDS_BY_ROUTE_NAMES = [
        Hotel::class => [
            'hotel_edit'                => ['description', 'logoImage'],
            'hotel_images'              => ['images'],
            'hotel_contact_information' => [
                'zipCode',
                'cityId',
                'street',
                'settlement',
                'house',
                'contactInformation',
//                'mapUrl'
            ],
        ],
        RoomType::class => [
            'room_type_edit'       => ['description', 'roomSpace', 'facilities'],
            'room_type_image_edit' => ['onlineImages'],
        ],
    ];

    /** @var DocumentManager */
    private $dm;
    private $documentFieldsManager;
    private $translator;
    private $warningsCompiler;
    private $billingApi;
    private $clientManager;

    /**
     * @var MBSiteStyleFormHolder
     */
    private $styleHolder;

    public function __construct(
        DocumentManager $dm, 
        DocumentFieldsManager $documentFieldsManager, 
        TranslatorInterface $translator,
        WarningsCompiler $warningsCompiler,
        BillingApi $billingApi,
        ClientManager $clientManager,
        MBSiteStyleFormHolder $styleHolder
    ) {
        $this->dm = $dm;
        $this->documentFieldsManager = $documentFieldsManager;
        $this->translator = $translator;
        $this->warningsCompiler = $warningsCompiler;
        $this->billingApi = $billingApi;
        $this->clientManager = $clientManager;
        $this->styleHolder = $styleHolder;
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
        return $this->dm->getRepository(FormConfig::class)->getForMBSite();
    }

    public function fetchPaymentFormConfig(): PaymentFormConfig
    {
        $formConfig = $this->dm->getRepository(PaymentFormConfig::class)->findOneBy(['forMbSite' => true]);
        if ($formConfig === null) {
            $formConfig = new PaymentFormConfig();
            $formConfig
                ->setForMbSite(true)
                ->setUseAccordion(true)
                ->setIsFullWidth(true)
                ->setTheme(FormConfig::THEMES[self::DEFAULT_BOOTSTRAP_THEME])
                ->setFrameHeight(600);

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
     * @return SiteConfig
     */
    public function createOrUpdateForHotel(Hotel $hotel = null)
    {
        $config = $this->getSiteConfig();
        if ($config === null) {
            $config = new SiteConfig();
            $this->dm->persist($config);

            $clientSite = $this->clientManager->getClientSite();
            if ($clientSite === null) {
                throw new \RuntimeException('There is no created client site');
            }

            $host = parse_url($clientSite->getUrl())['host'];
            $siteDomain = substr($host, 0, strpos($host, SiteConfig::DOMAIN));
            $config->setSiteDomain($siteDomain);
        }

        if ($hotel !== null) {
            $config->addHotel($hotel);
        }

        /** @var PaymentFormConfig $paymentForm */
        $paymentForm = $this->fetchPaymentFormConfig();
        $paymentForm->setHotels($config->getHotels()->toArray());
        $this->dm->persist($paymentForm);

        $this->updateSiteFormConfig($config, $this->fetchFormConfig());


        return $config;
    }

    /**
     * @param $isEnabled
     * @throws \UnexpectedValueException
     */
    public function changeSiteAvailability($isEnabled)
    {
        $clientSite = $this->clientManager->getClientSite();
        if (!is_null($clientSite)) {
            $clientSite->setIs_enabled($isEnabled);
            $result = $this->billingApi->updateClientSite($clientSite);
            if (!$result->isSuccessful()) {
                throw new \UnexpectedValueException(
                    'Incorrect errors from billing: '.json_encode($result->getErrors())
                );
            }
        }
    }

    /**
     * @param string $siteDomain
     * @return bool
     */
    public function checkSiteDomain(string $siteDomain)
    {
        $tempSiteConfig = new SiteConfig();
        $tempSiteConfig->setSiteDomain($siteDomain);

        $sitesResult = $this->billingApi->getSitesByUrlResult($this->compileSiteAddress($tempSiteConfig));

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
    public function updateSiteFormConfig(SiteConfig $config, FormConfig $formConfig, array $paymentTypes = null)
    {
        $siteAddress = $this->compileSiteAddress($config);

        $roomTypes = [];
        foreach ($config->getHotels() as $hotel) {
            $roomTypes = array_merge($roomTypes, $hotel->getRoomTypes()->toArray());
        }

        $formConfig
            ->setResultsUrl($siteAddress)
            ->setHotels($config->getHotels()->toArray())
            ->setRoomTypeChoices($roomTypes);

        if (!is_null($paymentTypes)) {
            $formConfig->setPaymentTypes($paymentTypes);
        }

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
            ? $this->compileSiteAddress($this->getSiteConfig())
            : null;
    }

    /**
     * @param string $siteDomain
     * @return string
     */
    public function compileSiteAddress(SiteConfig $config)
    {
        $format = '%s://%s';

        if ($config->getDomain() !== SiteConfig::FAKE_DOMAIN_FOR_DEV) {
            $format .= '%s';
        }

        return sprintf($format, $config->getScheme(), $config->getSiteDomain(), $config->getDomain());
    }
}
