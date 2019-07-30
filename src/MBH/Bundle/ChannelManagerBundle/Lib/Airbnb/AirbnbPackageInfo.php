<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\ICalTouristData;

class AirbnbPackageInfo extends AbstractICalTypePackageInfo
{
    /**
     * @return string
     */
    protected function getChannelManagerName(): string
    {
        return Airbnb::NAME;
    }

    /**
     * @return bool|\DateTime
     */
    public function getBeginDate()
    {
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTSTART'])->modify('midnight');
    }

    /**
     * @return bool|\DateTime
     */
    public function getEndDate()
    {
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTEND'])->modify('midnight');
    }

    /**
     * @return mixed
     */
    public function getChannelManagerId()
    {
        return $this->packageData['UID'];
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return (!empty($this->note) ?  ($this->note . "\n") : '')  . ($this->packageData['DESCRIPTION'] ?? '');
    }

    /**
     * @return ICalTouristData
     */
    protected function getTouristFetchData(): ICalTouristData
    {
        $rawPayerContactsData = explode('\n', $this->packageData['DESCRIPTION']);
        foreach ($rawPayerContactsData as $rawPayerContactData) {
            if (!empty($rawPayerContactData)) {
                $payerContactAsKeyValue = explode(':', $rawPayerContactData);
                $payerContacts[$payerContactAsKeyValue[0]] = trim($payerContactAsKeyValue[1]);
            }
        }

        $rawPayerNameData = explode(' ', trim($this->packageData['SUMMARY']));
        $payerName = $rawPayerNameData[0];
        //** TODO: HotFix when description has one word. */
        $payerSurname = $rawPayerNameData[1] ?? $payerName;

        return (new ICalTouristData())
            ->setPayerSurname($payerSurname)
            ->setPayerName($payerName)
            ->setPayerEmail($payerContacts['EMAIL'] ?? null)
            ->setPayerPhone($payerContacts['PHONE'] ?? null);
    }
}
