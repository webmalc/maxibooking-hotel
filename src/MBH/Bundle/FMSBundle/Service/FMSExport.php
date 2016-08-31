<?php

namespace MBH\Bundle\FMSBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\Container;

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
        return $this->container->get('twig')->render('MBHVegaBundle::vega_export.xml.twig', [
            'packages' => $packages,
        ]);
    }

    public function sendEmail(\DateTime $startDate, \DateTime $endDate)
    {
        $transporter = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
            ->setUsername('test@gmail.com')
            ->setPassword('password');

        /** @var \Swift_Mailer $mailer */
        $mailer = \Swift_Mailer::newInstance($transporter);

        $xml = $this->getKonturXML($startDate, $endDate);

        $message = \Swift_Message::newInstance('XML-файл для ФМС')
            ->attach(\Swift_Attachment::newInstance($xml, 'xmlForFMS.xml', 'text/xml'))
            ->setFrom('test@gmail.com')
            ->setTo('test1@gmail.com')
            ->setBody(
                $this->container->get('templating')
                    ->render('@MBHFMS/test/text.html.twig'),
                'text/html');
        $mailer->send($message);
    }
}