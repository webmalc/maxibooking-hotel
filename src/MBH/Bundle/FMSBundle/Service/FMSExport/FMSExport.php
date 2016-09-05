<?php

namespace MBH\Bundle\FMSBundle\Service\FMSExport;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\Container;
use MBH\Bundle\BaseBundle\Service\Messenger\NotifierMessage;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;

class FMSExport
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return string
     */
    private function getKonturXML(\DateTime $startDate, \DateTime $endDate)
    {
        /** @var DocumentManager $dm */
        $dm = $this->container->get('doctrine.odm.mongodb.document_manager');

        $packages = $dm->getRepository('MBHPackageBundle:Package')
            ->createQueryBuilder()
            ->field('end')->gte($startDate)
            ->field('end')->lte($endDate->modify('tomorrow -1 minute'))
            ->getQuery()
            ->execute();
        return $this->container->get('twig')->render('@MBHFMS/xml/fms_russian_example.xml.twig');
    }

    public function sendEmail(\DateTime $startDate , \DateTime $endDate )
    {
        /** @var Notifier $mailer */
        $mailer = $this->container->get('mbh.notifier.mailer');

        $message = new NotifierMessage();
        $message->setSubject('fmsXML_letter_title');
        $message->setTemplate('MBHBaseBundle:Mailer:fmsLetter.html.twig');
        $message->setText('fmsXML_letter_message');

        $xml = $this->getKonturXML($startDate, $endDate);
        $message->addAttachedFiles(new AttachedFile($xml, 'xmlForFMS.xml', 'text/xml'));

        $mailer->setMessage($message)->notify();
    }
}