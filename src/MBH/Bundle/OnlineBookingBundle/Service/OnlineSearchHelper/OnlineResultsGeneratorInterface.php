<?php

namespace MBH\Bundle\OnlineBookingBundle\Service\OnlineSearchHelper;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBookingBundle\Lib\OnlineSearchFormData;

interface OnlineResultsGeneratorInterface
{
    public function getResults(OnlineSearchFormData $formData): ArrayCollection;

    public function getType(): string;
}