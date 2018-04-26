<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\DocumentFieldsManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SiteManager
{
    const DEFAULT_RESULTS_PAGE = '/results';
    const DEFAULT_BOOTSTRAP_THEME = 'cerulean';
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

    public function __construct(DocumentManager $dm, DocumentFieldsManager $documentFieldsManager, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->documentFieldsManager = $documentFieldsManager;
        $this->translator = $translator;
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
     */
    public function getHotelsSettingsInfo(SiteConfig $config = null)
    {
        $settingsInfo = [];
        if (!is_null($config) && $config->getIsEnabled()) {
            foreach ($config->getHotels() as $hotel) {
                $numberOfWarnings = $this->getNumberOfWarnings($hotel);
                foreach ($hotel->getRoomTypes() as $roomType) {
                    $numberOfWarnings += $this->getNumberOfWarnings($roomType);
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
        if ($siteConfig->getIsEnabled()) {
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
     * @param $document
     * @return array
     */
    public function getDocumentFieldsCorrectnessTypesByRoutesNames($document): array
    {
        $documentClass = ClassUtils::getClass($document);
        $fieldsDataByRouteNames = self::MANDATORY_FIELDS_BY_ROUTE_NAMES[$documentClass];

        $result = ['document' => $document, 'fieldsData' => $this->checkFieldsCorrectness($fieldsDataByRouteNames, $document)];

        return $result;
    }

    /**
     * @param $document
     * @return integer
     */
    private function getNumberOfWarnings($document)
    {
        $fieldsCorrectnessByRoutes = $this->getDocumentFieldsCorrectnessTypesByRoutesNames($document);

        return array_reduce($fieldsCorrectnessByRoutes['fieldsData'], function($result, $fieldsByCorrectnessType) {
            foreach ($fieldsByCorrectnessType as $correctnessType => $fields) {
                if ($correctnessType !== 'correct') {
                    $result += count($fields);
                }
            }

            return $result;
        });
    }
}