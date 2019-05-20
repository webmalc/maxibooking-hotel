<?php
/**
 * Date: 20.05.19
 */

namespace MBH\Bundle\OnlineBundle\EventListener;


use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Common\EventSubscriber;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Document\SiteContent;

class SiteConfigSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate => 'preUpdate',
        ];
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $siteConfig = $args->getDocument();
        if ($siteConfig instanceof SiteConfig) {
            if ($args->hasChangedField('hotels')) {

                $dm = $args->getDocumentManager();
                $repoSiteContent = $dm->getRepository(SiteContent::class);

                $amount = $siteConfig->getContents(true)->count();

                foreach ($siteConfig->getHotels() as $hotel) {
                    $siteContent = $repoSiteContent->findOneBy(['hotel' => $hotel]);
                    if ($siteContent === null) {
                        $siteContent = new SiteContent();
                        $siteContent->setHotel($hotel);

                        $dm->persist($siteContent);
                        $siteConfig->getContents(true)->add($siteContent);
                    }
                }

                if ($siteConfig->getContents(true)->count() <= $amount) {
                    return;
                }

                $meta = $dm->getClassMetadata(SiteConfig::class);
                $dm->getUnitOfWork()->computeChangeSet($meta, $siteConfig);
                $dm->getUnitOfWork()->commit();
            }
        }
    }

}
