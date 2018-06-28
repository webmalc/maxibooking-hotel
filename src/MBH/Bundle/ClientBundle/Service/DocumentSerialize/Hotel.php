<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\HotelBundle\Document\Hotel as HotelBase;

/**
 * Class Hotel
 *
 * @property HotelBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Hotel extends Common
{
    use TraitAddress;

    protected const METHOD = [
        'getFullTitle',
        'getInternationalTitle',
        'getInternationalStreetName',
    ];

    /**
     * @return string
     */
    public function getArrivalTime(): string
    {
        return $this->entity->getPackageArrivalTime() ?? '';
    }

    /**
     * @return string
     */
    public function getDepartureTime(): string
    {
        return $this->entity->getPackageDepartureTime() ?? '';
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->entity->getContactInformation() !== null
            ? $this->entity->getContactInformation()->getPhoneNumber() ?? ''
            : '';
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        $url = '/bundles/mbhbase/images/empty_logo.png';
        if ($this->entity->getLogoImage() !== null) {
            // должно работать
            $helper = $this->container->get('Vich\UploaderBundle\Templating\Helper\UploaderHelper');
            $url = $helper->asset($this->entity->getLogoImage(), 'imageFile');
        } elseif ($this->entity->getLogo() !== null) {
            $url = $this->entity->getPath();
        }
//        $return = '<img src="' . $url . '" alt="Hotel logo" />';

//        return $url;
        return '<div style="width: 95px; height: 80px; background-color: lightgrey;"><img src="' . $url . '" alt="Hotel logo" /></div>';
    }

    protected function getSourceClassName()
    {
        return HotelBase::class;
    }
}
