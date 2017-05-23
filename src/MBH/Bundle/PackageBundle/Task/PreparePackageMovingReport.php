<?php

namespace MBH\Bundle\PackageBundle\Task;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Service\PackageMoving;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PreparePackageMovingReport implements ConsumerInterface
{
    /** @var  PackageMoving $packageZip */
    private $packageZip;
    /** @var  DocumentManager $dm */
    private $dm;

    public function __construct(PackageMoving $packageZip, DocumentManager $dm)
    {
        $this->packageZip = $packageZip;
        $this->dm = $dm;
    }

    /**
     * @param AMQPMessage $message The message
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $message)
    {
        $message = unserialize($message->body);
        $packageMovingInfo = $this->dm
            ->find('MBHPackageBundle:PackageMovingInfo', $message['packageMovingInfoId']);

        $packageMovingInfo = $this->packageZip->fillMovingPackageData($packageMovingInfo);
        $this->packageZip->sendPackageMovingMail($packageMovingInfo, 'mailer.package_moving_report.text',
            'mailer.package_moving_report.subject', 'MBHBaseBundle:Mailer:package_moving_report.html.twig');

        return true;
    }
}