<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Lib\SocialNetworking;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\OnlineBundle\Document\SocialNetworkingService;

class HolderSNSs
{
    /**
     * @var ArrayCollection|SocialNetworkingService[]
     */
    private $snss;

    public function __construct()
    {
        $this->snss = new ArrayCollection();
    }

    /**
     * @return ArrayCollection|SocialNetworkingService[]
     */
    public function getSnss(): ArrayCollection
    {
        return $this->snss;
    }

    public function deleteEmptyUrl(): void
    {
        $this->snss = $this->getSnss()->filter(function ($sns) {
            /** @var SocialNetworkingService $sns */
            return $sns->getUrl() !== null;
        });
    }
}