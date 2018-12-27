<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

interface OnlineDataProviderWrapperInterface
{
    public function getResults(OnlineSearchFormData $formData): array;

    public function getType(): string;
}