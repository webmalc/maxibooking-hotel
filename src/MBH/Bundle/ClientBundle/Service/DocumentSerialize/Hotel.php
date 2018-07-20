<?php
/**
 * Created by PhpStorm.
 * Date: 27.04.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\HotelBundle\Document\Hotel as HotelBase;
use MBH\Bundle\PackageBundle\Lib\AddressInterface;

/**
 * Class Hotel
 *
 * @property HotelBase $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Hotel extends Common implements AddressInterface
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
        $filter = '';
        if ($this->entity->getLogoImage() !== null) {
            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            $url = $helper->asset($this->entity->getLogoImage(), 'imageFile');
            $filter = '|imagine_filter(\'scaler\')';
        } elseif ($this->entity->getLogo() !== null) {
            $url = $this->entity->getPath();
        }

        $content = "<img style=\"max-width: 95px; max-height: 80px;\" src=\"{{ absolute_url(asset('{$url}'{$filter})) }}\" alt=\"Hotel logo\"/>";
        /* здесь твиг только для генерации ссылки*/
        $twig = $this->container->get('twig');
        $renderedImg = $twig->createTemplate($content)->render(['url' => $url]);

        return $renderedImg;
    }

    protected function getSourceClassName()
    {
        return HotelBase::class;
    }
}
