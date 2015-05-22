<?php

namespace MBH\Bundle\CashBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\BaseBundle\Service\PdfGenerator;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CashDocumentSubscriber
 * @package MBH\Bundle\PackageBundle\EventListener
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class CashDocumentSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist'
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var CashDocument $document */
        $document = $args->getDocument();
        if($document instanceof CashDocument && $document->getMethod() == 'cashless' && $document->getOperation() == 'in') { //поступление безнал
            $this->createPdfOrderDocument($document, $args->getDocumentManager());
        }
    }

    private function createPdfOrderDocument(CashDocument $document,DocumentManager $dm)
    {
        if(!$document->getPayer())
            return;

        $orderDocument = new OrderDocument();
        $orderDocument->setName('Счёт на оплату');
        $orderDocument->setOriginalName('bill_'.$document->getNumber().'_'.$document->getDocumentDate()->format('d.m.Y').'.pdf');
        $uniqidName = uniqid();
        $orderDocument->setName($uniqidName.'.pdf');
        $orderDocument->setType('invoice_for_payment');
        $orderDocument->setMimeType('application/pdf');
        $orderDocument->setExtension('pdf');
        $orderDocument->setComment('Счёт №'.$document->getNumber().' от '.$document->getDocumentDate()->format('d.m.Y').' ('.$document->getTotal().' руб.)');
        $orderDocument->setTourist($document->getTouristPayer());
        $orderDocument->setOrganization($document->getOrganizationPayer());

        /** @var PdfGenerator $generator */
        $generator = $this->container->get('mbh.pdf_generator');
        $generator->setPath($orderDocument->getUploadRootDir());

        if($document->getPayer() instanceof Organization) {
            $template = 'organization';
        } elseif($document->getPayer() instanceof Tourist) {
            $template = 'individual';
        } else
            return;

        $myOrganization = $dm->getRepository('MBHPackageBundle:Organization')->getOrganizationByOrder($document->getOrder());

        if($myOrganization && $generator->save($uniqidName, $template, ['cashDocument' => $document, 'myOrganization' => $myOrganization])) {
            $document->getOrder()->addDocument($orderDocument);
            //$dm->persist($document->getOrder());
            //$dm->flush();
        }
    }
}