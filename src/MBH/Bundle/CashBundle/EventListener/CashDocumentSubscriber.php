<?php

namespace MBH\Bundle\CashBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Event\PreUpdateEventArgs;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\PdfGenerator;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\Events;

/**
 * Class CashDocumentSubscriber
 * @package MBH\Bundle\PackageBundle\EventListener
 */
class CashDocumentSubscriber implements EventSubscriber
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            Events::prePersist => 'prePersist',
            Events::preUpdate => 'preUpdate'
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** @var CashDocument $document */
        $document = $args->getDocument();

        if ($document instanceof CashDocument && $document->getMethod() == 'cashless' && $document->getOperation() == 'in' && $document->getPayer()) {

            try {
                $this->createPdfOrderDocument($document, $args->getDocumentManager());
            } catch (Exception $e) {
                $session = $this->container->get('session');
                $session->getFlashBag()->add('danger', $this->container->get('translator')->trans('cashDocumentSubscriber.document.dlia.pechati.ne.sozdan') . ' ' . $e->getMessage());
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $cashDocument = $args->getDocument();
        if ($cashDocument instanceof CashDocument) {
            $this->trySendOnConfirmationNotification($cashDocument);
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $cashDocument = $args->getDocument();

        if ($cashDocument instanceof CashDocument) {
            /** @var CashDocument $cashDocument */
            if ($args->hasChangedField('isConfirmed')) {
                $this->trySendOnConfirmationNotification($cashDocument);
            }
        }
    }

    /**
     * @param CashDocument $cashDocument
     */
    private function trySendOnConfirmationNotification(CashDocument $cashDocument)
    {
        if ($cashDocument->getIsConfirmed() && $cashDocument->isSendMail() && $cashDocument->getOperation() == 'in' && $cashDocument->getPayer()) {
            $this->container->get('mbh.cash')->sendMailAtCashDocumentConfirmation($cashDocument);
        }
    }

    /**
     * @param CashDocument $document
     * @param DocumentManager $dm
     * @return OrderDocument
     * @throws \MBH\Bundle\BaseBundle\Lib\Exception
     */
    private function createPdfOrderDocument(CashDocument $document, DocumentManager $dm)
    {
        $currency = $this->container->get('mbh.currency')->info();
        $translator = $this->container->get('translator');
        $orderDocument = new OrderDocument();
        $orderDocument->setName($this->container->get('translator')->trans('cashDocumentSubscriber.schet.na.oplatu'));
        $orderDocument->setOriginalName('bill_'.$document->getNumber().'_'.$document->getDocumentDate()->format('d.m.Y').'.pdf');
        $id = uniqid();
        $orderDocument->setName($id.'.pdf');
        $orderDocument->setType('invoice_for_payment');
        $orderDocument->setMimeType('application/pdf');
        $orderDocument->setExtension('pdf');
        $orderDocument->setComment($this->container->get('translator')->trans('cashDocumentSubscriber.schet') . ' №'.$document->getNumber(). ' ' . $this->container->get('translator')->trans('cashDocumentSubscriber.ot') . ' '.$document->getDocumentDate()->format('d.m.Y').' ('.$document->getTotal().' ' . $translator->trans($currency['text']) . ')');
        $orderDocument->setTourist($document->getTouristPayer());
        $orderDocument->setOrganization($document->getOrganizationPayer());

        if ($document->getPayer() instanceof Organization) {
            $template = 'organization';
        } elseif ($document->getPayer() instanceof Tourist) {
            $template = 'individual';
        }
        if (!isset($template)) {
            throw new Exception('CashDocument payer type is unknown.');
        }

        $myOrganization = $dm->getRepository('MBHPackageBundle:Organization')->getOrganizationByOrder($document->getOrder());

        if (!$myOrganization) {
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.naidena.organizacia.dlia.etogo.otela'));
        }

        if(!$myOrganization->getBank())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.bank.polichatela'));

        if(!$myOrganization->getBankBik())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.bik.polichatela'));

        if(!$myOrganization->getCorrespondentAccount())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.kor.shet'));

        if(!$myOrganization->getInn())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.inn'));

        if(!$myOrganization->getKpp())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.kpp'));

        if(!$myOrganization->getCheckingAccount())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovlen.raschetnii.shet'));

        if(!$myOrganization->getName())
            throw new Exception($this->container->get('translator')->trans('cashDocumentSubscriber.ne.ustanovleno.nazvanie.organizacii'));

        /** @var \AppKernel $kernel */
        $kernel = $this->container->get('kernel');
        $client = $kernel->getClient();
        /** @var PdfGenerator $generator */
        $generator = $this->container->get('mbh.pdf_generator');
        $path = $orderDocument->getUploadRootDir($client);
        $generator->setPath($path);
        $generator->save($id, $template, ['cashDocument' => $document, 'myOrganization' => $myOrganization]);

        $file = new ProtectedFile();
        $uploadedFile = new UploadedFile($path, $fileName, 'pdf');
        $file->setImageFile($uploadedFile);

        $orderDocument->setCashDocument($document);
        //$document->setOrderDocument($orderDocument);

        $order = $document->getOrder();
        $order->addDocument($orderDocument);
        $dm->persist($order);
        $dm->flush();

        return $orderDocument;
    }
}