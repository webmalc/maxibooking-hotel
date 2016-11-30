<?php

namespace MBH\Bundle\PackageBundle\Services;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Lib\PackageConfirmerException;

/**
 * Class PackageConfirmer
 * @package MBH\Bundle\PackageBundle\Services
 *
 * Подтверждает бронь.
 */
class PackageConfirmer
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * PackageConfirmer constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @param Package $package
     * @return $this
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return void
     * @throws PackageConfirmerException
     */
    public function confirm()
    {
        if ($this->package instanceof Package) {
            $order = $this->package->getOrder();
            $order->setConfirmed(true);
            $this->dm->persist($order);
            $this->dm->flush();
        } else {
            throw new PackageConfirmerException('Need setPackage before');
        }

    }


}