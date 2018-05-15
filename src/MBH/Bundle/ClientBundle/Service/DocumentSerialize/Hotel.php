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
    public function getPhoneNumber(): string
    {
        return $this->entity->getContactInformation() ?? $this->entity->getContactInformation()->getPhoneNumber() ?? '';
    }

    /**
     * @return string
     */
    public function getLogo(): string
    {
        $url = '';
        if ($this->entity->getLogoImage() !== null) {
            // должно работать
            $helper = $this->container->get('Vich\UploaderBundle\Templating\Helper\UploaderHelper');
            $url = $helper->asset($this->entity->getLogoImage(), 'imageFile');
        } elseif ($this->entity->getLogo() !== null) {
            $url = $this->entity->getPath();
        }
        $return = '<img src="' . $url . '" alt="Hotel logo" />';

        return $url === '' ? '' : $return;
    }
}
