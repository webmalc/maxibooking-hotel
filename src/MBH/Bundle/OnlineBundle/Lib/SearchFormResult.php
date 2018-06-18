<?php
/**
 * Created by PhpStorm.
 * Date: 13.06.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


class SearchFormResult implements \JsonSerializable
{
    /**
     * @var bool
     */
    private $orderFound = false;

    /**
     * @var float
     */
    private $total;

    /**
     * @var string
     */
    private $packageId;

    /**
     * @param float $total
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    /**
     * @param string $packageId
     */
    public function setPackageId(string $packageId): void
    {
        $this->packageId = $packageId;
    }

    public function orderIsFound(): void
    {
        $this->orderFound = true;
    }


    public function jsonSerialize()
    {
        $result = [];
        if (!$this->orderFound) {
            $result['error'] = 'not found order';
        } else {
            $result['needIsPaid'] = $this->isNeedIsPaid();
            if ($this->isNeedIsPaid()) {
                $result['data'] = [
                    'total'     => $this->total,
                    'packageId' => $this->packageId,
                ];
            } else {
                $result['data'] = 'order has been paid';
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function isNeedIsPaid(): bool
    {
        return $this->total !== null && $this->packageId !== null;
    }
}