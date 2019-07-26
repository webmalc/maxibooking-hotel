<?php

namespace MBH\Bundle\ChannelManagerBundle\Services\Airbnb;

use MBH\Bundle\ChannelManagerBundle\Lib\ICalType\AbstractICalTypePackageInfo;
use MBH\Bundle\PackageBundle\Document\Tourist;

class AirbnbPackageInfo extends AbstractICalTypePackageInfo
{

    public function getBeginDate()
    {
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTSTART'])->modify('midnight');
    }

    public function getEndDate()
    {
        return \DateTime::createFromFormat('Ymd', $this->packageData['DTEND'])->modify('midnight');
    }

    public function getChannelManagerId()
    {
        return $this->packageData['UID'];
    }

    public function getNote(): string
    {
        return (!empty($this->note) ?  ($this->note . "\n") : '')  . ($this->packageData['DESCRIPTION'] ?? '');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getTourists(): array
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

        $rawPayerNameData = explode(' ', trim($this->packageData['SUMMARY']));
        $payerName = $rawPayerNameData[0];
        //** TODO: HotFix when description has one word. */
        $payerSurname = $rawPayerNameData[1] ?? $payerName;

        $payer = $this->dm
            ->getRepository(Tourist::class)
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
}
