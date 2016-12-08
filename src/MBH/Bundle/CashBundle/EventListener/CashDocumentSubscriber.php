<?php

namespace MBH\Bundle\CashBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BaseBundle\Service\PdfGenerator;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $currency = $this->container->get('mbh.currency')->info();
        $orderDocument = new OrderDocument();
        $orderDocument->setName('Счёт на оплату');
        $orderDocument->setOriginalName('bill_'.$document->getNumber().'_'.$document->getDocumentDate()->format('d.m.Y').'.pdf');
        $id = uniqid();
        $orderDocument->setName($id.'.pdf');
        $orderDocument->setType('invoice_for_payment');
        $orderDocument->setMimeType('application/pdf');
        $orderDocument->setExtension('pdf');
        $orderDocument->setComment('Счёт №'.$document->getNumber().' от '.$document->getDocumentDate()->format('d.m.Y').' ('.$document->getTotal().' ' . $currency['text'] . ')');
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
            throw new Exception('Не найдена организация для этого отеля из раздела "Мои организации".');
        }

        if(!$myOrganization->getBank())
            throw new Exception('Не установлен "Банк получателя".');

        if(!$myOrganization->getBankBik())
            throw new Exception('Не установлен "Бик банка".');

        if(!$myOrganization->getCorrespondentAccount())
            throw new Exception('Не установлен "Корр. счёт".');

        if(!$myOrganization->getInn())
            throw new Exception('Не установлен "ИНН".');

        if(!$myOrganization->getKpp())
            throw new Exception('Не установлен "КПП".');

        if(!$myOrganization->getCheckingAccount())
            throw new Exception('Не установлен "Расчётный счёт".');

        if(!$myOrganization->getName())
            throw new Exception('Не установлено "Название организации".');

        /** @var PdfGenerator $generator */
        $generator = $this->container->get('mbh.pdf_generator');
        $generator->setPath($orderDocument->getUploadRootDir());
        $generator->save($id, $template, ['cashDocument' => $document, 'myOrganization' => $myOrganization]);

        $orderDocument->setCashDocument($document);
        //$document->setOrderDocument($orderDocument);

        $order = $document->getOrder();
        $order->addDocument($orderDocument);
        $dm->persist($order);
        $dm->flush();

        return $orderDocument;
    }
}