<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\HomeAway;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;
use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\ICalTouristData;

class HomeAwayPackageInfo extends AbstractICalTypePackageInfo
{
    /**
     * @return string
     */
    protected function getChannelManagerName(): string
    {
        return HomeAway::NAME;
    }

    /**
     * @return bool|\DateTime
     */
    public function getBeginDate()
    {
        throw new \Exception('implement me');

        return \DateTime::createFromFormat('Ymd', $this->packageData['DTSTART'])->modify('midnight');
    }

    /**
     * @return bool|\DateTime
     */
    public function getEndDate()
    {
        throw new \Exception('implement me');

        return \DateTime::createFromFormat('Ymd', $this->packageData['DTEND'])->modify('midnight');
    }

    /**
     * @return ICalTouristData
     */
    protected function getTouristFetchData(): ICalTouristData
    {
        throw new \Exception('implement me');

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

    /**
     * @return mixed
     */
    public function getChannelManagerId()
    {
        throw new \Exception('implement me');

        return $this->packageData['UID'];
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        throw new \Exception('implement me');

        return (!empty($this->note) ?  ($this->note . "\n") : '')  . ($this->packageData['DESCRIPTION'] ?? '');
    }
}
