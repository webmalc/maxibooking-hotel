<?php


namespace MBH\Bundle\PackageBundle\Lib;


interface HotelAccessibleInterface
{
    public function getAccessibleHotels(): ?iterable;
}