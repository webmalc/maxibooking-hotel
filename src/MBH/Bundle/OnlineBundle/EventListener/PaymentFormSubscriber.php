<?php
/**
 * Created by PhpStorm.
 * Date: 21.11.18
 */

namespace MBH\Bundle\OnlineBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use MBH\Bundle\OnlineBundle\Document\PaymentFormConfig;
use MBH\Bundle\OnlineBundle\Document\SiteConfig;
use MBH\Bundle\OnlineBundle\Form\SiteForm;
use MBH\Bundle\OnlineBundle\Services\SiteManager;
use Symfony\Component\DependencyInjection\ContainerInterface;


class PaymentFormSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $entity = $args->getDocument();

        if ($entity instanceof PaymentFormConfig) {
            $this->remoteIdFromSiteConfig($entity, $args);
        }
    }

    private function remoteIdFromSiteConfig(PaymentFormConfig $formConfig, LifecycleEventArgs $args): void
    {
        if (!$formConfig->isForMbSite()) {
            return;
        }

        /** @var SiteConfig $siteConfig */
        $siteConfig = $this->container->get('mbh.site_manager')->getSiteConfig();

        if ($siteConfig !== null && $siteConfig->getPaymentFormId() !== null) {
            $siteConfig->setPaymentFormId(null);

            $args->getDocumentManager()->flush($siteConfig);
        }
    }
}