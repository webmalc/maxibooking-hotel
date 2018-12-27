<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper\DataProviders;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

interface DataProviderInterface
{
    public function search(OnlineSearchFormData $formData): array;

    public function getType(): string;
}