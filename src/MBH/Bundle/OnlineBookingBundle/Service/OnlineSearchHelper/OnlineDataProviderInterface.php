<?php


namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;
use MBH\Bundle\PackageBundle\Document\SearchQuery;

interface OnlineDataProviderInterface
{
    public function search(OnlineSearchFormData $formData, SearchQuery $searchQuery): array;
    public function getType(): string;
}