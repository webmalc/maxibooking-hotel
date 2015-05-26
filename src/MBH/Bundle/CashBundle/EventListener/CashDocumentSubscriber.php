<?php

namespace MBH\Bundle\CashBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\BaseBundle\Service\PdfGenerator;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Lib\Exception;

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
            'postPersist'
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
                $session->getFlashBag()->add('danger', 'Документ для печати не создан. ' . $e->getMessage());
            }
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
        $orderDocument = new OrderDocument();
        $orderDocument->setName('Счёт на оплату');
        $orderDocument->setOriginalName('bill_'.$document->getNumber().'_'.$document->getDocumentDate()->format('d.m.Y').'.pdf');
        $id = uniqid();
        $orderDocument->setName($id.'.pdf');
        $orderDocument->setType('invoice_for_payment');
        $orderDocument->setMimeType('application/pdf');
        $orderDocument->setExtension('pdf');
        $orderDocument->setComment('Счёт №'.$document->getNumber().' от '.$document->getDocumentDate()->format('d.m.Y').' ('.$document->getTotal().' руб.)');
        $orderDocument->setTourist($document->getTouristPayer());
        $orderDocument->setOrganization($document->getOrganizationPayer());

        /** @var PdfGenerator $generator */
        $generator = $this->container->get('mbh.pdf_generator');
        $generator->setPath($orderDocument->getUploadRootDir());

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
            throw new Exception('Не найдена организация для этого отеля из раздела "Мои организации".');
        }
        $generator->save($id, $template, ['cashDocument' => $document, 'myOrganization' => $myOrganization]);

        $order = $document->getOrder();
        $order->addDocument($orderDocument);
        $dm->persist($order);
        $dm->flush();

        return $orderDocument;
    }
}