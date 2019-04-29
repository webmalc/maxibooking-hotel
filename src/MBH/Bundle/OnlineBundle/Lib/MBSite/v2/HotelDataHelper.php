<?php
/**
 * Date: 17.04.19
 */

namespace MBH\Bundle\OnlineBundle\Lib\MBSite\v2;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class HotelDataHelper extends MbSiteData
{
    /**
     * @var Hotel
     */
    private $hotel;

    /**
     * @var BillingApi
     */
    private $billingApi;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        Hotel $hotel,
        BillingApi $billingApi,
        string $locale,
        UploaderHelper $uploaderHelper,
        CacheManager $cacheManager
    ) {
        $this->hotel = $hotel;
        $this->billingApi = $billingApi;
        $this->locale = $locale;

        parent::__construct($uploaderHelper, $cacheManager);
    }

    /**
     * @param bool $isFull
     * @return array
     */
    public function getPreparedData(): array
    {
        $data = [
            'id'          => $this->hotel->getId(),
            'title'       => $this->hotel->getFullTitle() ?? $this->hotel->getInternationalTitle(),
            'isDefault'   => $this->hotel->getIsDefault(),
            'description' => $this->stripTags($this->hotel->getDescription()),
        ];

        $photosData = $this->getImagesData($this->hotel->getImages()->toArray(), true);

        $data['photos'] = [
            'top'     => [
                'amount' => count($photosData['top']),
                'list'   => $photosData['top'],
            ],
            'gallery' => [
                'amount' => count($photosData['gallery']),
                'list'   => $photosData['gallery'],
            ],
        ];

        $data['logo'] = $this->hotel->getLogoImage() !== null
            ? $this->generateUrl($this->hotel->getLogoImage(), self::FILTER_SCALER)
            : null;


        $this->compileContactInfo($data);
        $this->compileFacilities($data);

//        if (!empty($this->hotel->getMapImage())) {
//            $data['mapUrl'] = $this->generateUrl($this->hotel->getMapImage(), self::FILTER_SCALER);
//        }


        return $data;
    }


    private function compileContactInfo(array &$data): void
    {
        $phone = null;
        $email = null;
        $city = null;

        if ($this->hotel->getContactInformation() !== null) {
            $email = $this->hotel->getContactInformation()->getEmail() ?? null;
            $phone = $this->hotel->getContactInformation()->getPhoneNumber(true) ?? null;
        }

        if ($this->hotel->getCityId()) {
            $city = $this->billingApi->getCityById($this->hotel->getCityId())->getName();
        }

        $data['contacts'] = [
            'email'   => $email,
            'phone'   => $phone,
            'address' => [
                'city'    => $city,
                'street'  => $this->hotel->getStreet() ?? null,
                'house'   => $this->hotel->getHouse() ?? null,
                'corpus'  => $this->hotel->getCorpus() ?? null,
                'flat'    => $this->hotel->getFlat() ?? null,
                'zipCode' => $this->hotel->getZipCode() ?? null,
            ],
        ];
    }

    private function compileFacilities(array &$data): void
    {
        $data['facilities'] = [
            'amount' => count($this->hotel->getFacilities()),
            'list'   => $this->hotel->getFacilities()
        ];
    }
}
