<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\Site;

use MBH\Bundle\HotelBundle\Document\Hotel;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


class HotelImageDataDecorator extends ImageDataDecorator
{
    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * HotelManage constructor.
     * @param Hotel $hotel
     * @param UploaderHelper $uploaderHelper
     * @param CacheManager $cacheManager
     */
    public function __construct(Hotel $hotel, ?UploaderHelper $uploaderHelper, ?CacheManager $cacheManager)
    {
        $this->hotel = $hotel;

        parent::__construct($uploaderHelper, $cacheManager);
    }


    /**
     * @param bool $isFull
     * @return array
     */
    public function getJsonSerialized($isFull = false): array
    {
        $data = [
            'id'    => $this->hotel->getId(),
            'title' => $this->hotel->getFullTitle() ?? $this->hotel->getInternationalTitle(),
        ];

        if ($isFull) {
            $comprehensiveData = [
                'isEnabled'   => $this->hotel->getIsEnabled(),
                'isDefault'   => $this->hotel->getIsDefault(),
                'isHostel'    => $this->hotel->getIsHostel(),
                'description' => $this->hotel->getDescription(),
                'facilities'  => $this->hotel->getFacilities()
            ];
            if ($this->uploaderHelper !== null && $this->cacheManager !== null) {
                $comprehensiveData['photos'] = $this->getImagesData($this->hotel->getImages()->toArray());
                if ($this->hotel->getLogoImage() !== null) {
                    $comprehensiveData['logoUrl'] = $this->generateUrl($this->hotel->getLogoImage(), self::FILTER_SCALER);
                }
            } else {
                throw new \InvalidArgumentException('It\'s required uploader helper and current domain for serialization of the full information about the hotel!');
            }

//            if (!is_null($this->hotel->latitude)) {
//                $comprehensiveData['latitude'] = $this->hotel->latitude;
//            }
//            if (!is_null($this->hotel->longitude)) {
//                $comprehensiveData['longitude'] = $this->hotel->longitude;
//            }
            if (!empty($this->hotel->getStreet())) {
                $comprehensiveData['street'] = $this->hotel->getStreet();
            }
            if (!empty($this->hotel->getHouse())) {
                $comprehensiveData['house'] = $this->hotel->getHouse();
            }
            if (!empty($this->hotel->getCorpus())) {
                $comprehensiveData['corpus'] = $this->hotel->getCorpus();
            }
            if (!empty($this->hotel->getFlat())) {
                $comprehensiveData['flat'] = $this->hotel->getFlat();
            }
            if (!empty($this->hotel->getZipCode())) {
                $comprehensiveData['zipCode'] = $this->hotel->getZipCode();
            }
            if ($this->hotel->getContactInformation() !== null) {
                $contactsInfo = $this->hotel->getContactInformation();
                $contactsInfoArray = [];
                if (!empty($contactsInfo->getEmail())) {
                    $contactsInfoArray['email'] = $contactsInfo->getEmail();
                }
                if (!empty($contactsInfo->getPhoneNumber())) {
                    $contactsInfoArray['phone'] = $contactsInfo->getPhoneNumber();
                }
                if (!empty($contactsInfoArray)) {
                    $comprehensiveData['contacts'] = $contactsInfoArray;
                }
            }
            if (!empty($this->hotel->getMapImage())) {
                $comprehensiveData['mapUrl'] = $this->generateUrl($this->hotel->getMapImage(), self::FILTER_SCALER);
            }

            $data = array_merge($data, $comprehensiveData);
        }

        return $data;
    }
}