<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Document\AirbnbRoom;
use MBH\Bundle\ChannelManagerBundle\Document\Tariff;
use MBH\Bundle\ChannelManagerBundle\Lib\AbstractPackageInfo;
use MBH\Bundle\PackageBundle\Document\PackagePrice;

class AirbnbPackageInfo extends AbstractPackageInfo
{
    /** @var array */
    private $packageData;
    /** @var AirbnbRoom */
    private $airbnbRoom;
    /** @var Tariff */
    private $tariff;

    private $isCorrupted = false;
    private $packagePrice;
    private $isPackagePriceInit = false;

    /**
     * @param array $packageData
     * @param AirbnbRoom $airbnbRoom
     * @param Tariff $tariff
     * @return AirbnbPackageInfo
     */
    public function setInitData(array $packageData, AirbnbRoom $airbnbRoom, Tariff $tariff)
    {
        $this->packageData = $packageData;
        $this->airbnbRoom = $airbnbRoom;
        $this->tariff = $tariff;

        return $this;
    }

    public function getBeginDate()
    {
        return (\DateTime::createFromFormat('Ymd', $this->packageData['DTSTART']))->modify('midnight');
    }

    public function getEndDate()
    {
        return (\DateTime::createFromFormat('Ymd', $this->packageData['DTEND']))->modify('midnight');
    }

    public function getRoomType()
    {
        return $this->airbnbRoom->getRoomType();
    }

    public function getTariff()
    {
        return $this->tariff->getTariff();
    }

    public function getAdultsCount()
    {
        return 1;
    }

    public function getChildrenCount()
    {
        return 0;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPrices()
    {
        return $this->getPackagePrice()['packagePrices'];
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPrice()
    {
        return $this->getPackagePrice()['total'];
    }

    public function getNote()
    {
        return $this->packageData['DESCRIPTION'] ?? '';
    }

    public function getIsCorrupted()
    {
        return $this->isCorrupted;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTourists()
    {
        $rawPayerContactsData = explode('\n', $this->packageData['DESCRIPTION']);
        $payerContacts = [];
        foreach ($rawPayerContactsData as $rawPayerContactData) {
            if (!empty($rawPayerContactData)) {
                $payerContactAsKeyValue = explode(':', $rawPayerContactData);
                $payerContacts[$payerContactAsKeyValue[0]] = trim($payerContactAsKeyValue[1]);
            }
        }
        $phone = $payerContacts['PHONE'] ?? null;
        $email = $payerContacts['EMAIL'] ?? null;

        $rawPayerNameData = explode(' ', $this->packageData['SUMMARY']);
        $payerName = $rawPayerNameData[0];
        $payerSurname = $rawPayerNameData[1];

        $payer = $this->dm
            ->getRepository('MBHPackageBundle:Tourist')
            ->fetchOrCreate(
                $payerSurname,
                $payerName,
                null,
                null,
                $email,
                $phone
            );

        return [$payer];
    }

    public function getIsSmoking()
    {
        return false;
    }

    public function getChannelManagerId()
    {
        return $this->packageData['UID'];
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getPackagePrice()
    {
        if (!$this->isPackagePriceInit) {
            $pricesByCombinations = $this->container
                ->get('mbh.calculation')
                ->calcPrices(
                    $this->getRoomType(),
                    $this->getTariff(),
                    $this->getBeginDate(),
                    ($this->getEndDate())->modify('-1 day'),
                    $this->getAdultsCount()
                );

            $combination = $this->getAdultsCount() . '_' . $this->getChildrenCount();
            if (!is_array($pricesByCombinations) || !isset($pricesByCombinations[$combination])) {
                $pricesByDates = [];
                $packagePrices = [];

                /** @var \DateTime $date */
                foreach (new \DatePeriod($this->getBeginDate(), new \DateInterval('P1D'), $this->getEndDate()) as $date) {
                    $pricesByDates[$date->format('d_m_Y')] = 0;
                    $packagePrices[] = (new PackagePrice($date, 0, $this->getTariff()));
                }

                $this->packagePrice = [
                    'total' => 0,
                    'prices' => $pricesByDates,
                    'packagePrices' => $packagePrices
                ];

                $this->addPackageNote('airbnb_package_info.errors.can_not_calc_price');
                $this->isCorrupted = true;
            } else {
                $this->packagePrice = $pricesByCombinations[$combination];
            }

            $this->isPackagePriceInit = true;
        }

        return $this->packagePrice;
    }
}