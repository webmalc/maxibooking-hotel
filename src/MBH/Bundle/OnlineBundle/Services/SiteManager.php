<?php

namespace MBH\Bundle\OnlineBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\RuTranslateConverter\TranslateInterface;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\OnlineBundle\Document\FormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use Symfony\Component\Translation\TranslatorInterface;

class SiteManager
{
    const DEFAULT_RESULTS_PAGE = '/results';
    const DEFAULT_BOOTSTRAP_THEME = 'cerulean';

    /** @var DocumentManager */
    private $dm;
    /** @var TranslateInterface */
    private $translator;

    public function __construct(DocumentManager $dm, TranslatorInterface $translator)
    {
        $this->dm = $dm;
        $this->translator = $translator;
    }

    /**
     * @param Hotel $hotel
     * @return array
     */
    public function getHotelWarningsByRoutesNames(Hotel $hotel)
    {
        $warnings = [];
        $emptyFields = $this->getEmptyFieldsNames([
            'form.hotelType.description' => $hotel->getDescription(),
            'form.hotel_logo.image_file.help' => $hotel->getLogoUrl()
        ]);
        if (!empty($emptyFields)) {
            $warnings['hotel_edit'] = ['empty' => $emptyFields];
        }

        $emptyFields = $this->getEmptyFieldsNames([
            'form.hotel_contact_information.contact_info.group' => $hotel->getContactInformation(),
            'form.hotelExtendedType.latitude' => $hotel->getLatitude(),
            'form.hotelExtendedType.longitude' => $hotel->getLongitude()
        ]);
        if (!empty($emptyFields)) {
            $warnings['hotel_contact_information'] = ['empty' => $emptyFields];
        }

        if ($hotel->getImages()->count() === 0) {
            $warnings['hotel_images'] = ['empty' => $this->translator->trans('views.hotel.tabs.images', [], 'MBHHotelBundle')];
        }

        return $warnings;
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
                $settingsInfo[] = [
                    'hotel' => $hotel,
                    'numberOfWarnings' => count($this->getHotelWarningsByRoutesNames($hotel)),
                ];
            }
        }

        return $settingsInfo;
    }

    /**
     * @param array $fieldsDataByNames
     * @return array
     */
    private function getEmptyFieldsNames(array $fieldsDataByNames)
    {
        $emptyFieldsNames = [];
        foreach ($fieldsDataByNames as $fieldName => $fieldData) {
            if (empty($fieldData)) {
                $emptyFieldsNames[] = '"' . $this->translator->trans($fieldName) . '"';
            }
        }

        return $emptyFieldsNames;
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
}