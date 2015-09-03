<?php


namespace MBH\Bundle\PackageBundle\Component;

use MBH\Bundle\PackageBundle\Document\Package;

/**
 * Class ReportRoomTypeStatuses
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class ReportRoomTypeStatus
{
    const OPEN = 'open';
    const DEPT = 'dept';
    const PAID = 'paid';
    const NOT_OUT = 'not_out';
    const OUT_NOW = 'out_now';

    public function getStatusByPackage(Package $package)
    {
        if(!$package->getOrder() or !$package->getIsCheckIn()) {
            return self::OPEN;
        }

        $now = new \DateTime('midnight');
        if (!$package->getIsCheckOut() && $now->format('Ymd') > $package->getEnd()->format('Ymd')) {
            return self::NOT_OUT;
        }
        if ($package->getIsPaid()) {
                return $now->format('d.m.Y') == $package->getEnd()->format('d.m.Y') ?
                    self::OUT_NOW :
                    self::PAID;
        } else {
            return  self::DEPT;
        }
    }

    public function getAvailableStatues()
    {
        return [
            self::OPEN,
            self::DEPT,
            self::PAID,
            self::NOT_OUT,
            self::OUT_NOW,
        ];
    }
}