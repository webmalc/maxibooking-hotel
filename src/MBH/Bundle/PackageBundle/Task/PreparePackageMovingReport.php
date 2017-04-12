<?php

namespace MBH\Bundle\PackageBundle\Task;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Service\Messenger\Notifier;
use MBH\Bundle\ClientBundle\Service\PackageZip;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class PreparePackageMovingReport implements ConsumerInterface
{
    /** @var  PackageZip $packageZip */
    private $packageZip;
    /** @var  DocumentManager $dm */
    private $dm;
    /** @var  Notifier $mailer  */
    private $mailer;

    public function __construct(PackageZip $packageZip, DocumentManager $dm, Notifier $mailer)
    {
        $this->packageZip = $packageZip;
        $this->dm = $dm;
        $this->mailer = $mailer;
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
//        $this->sendMailNotification($packageMovingInfo);

        return true;
    }

    private function sendMailNotification($packageMovingInfo)
    {
        //TODO: мб сменить получателя
        $message = $this->mailer::createMessage();
        $message
            ->setText('mailer.packaging.text')
            ->setFrom('system')
            ->setSubject('mailer.packaging.subject')
            ->setType('info')
            ->setCategory('notification')
            ->setTemplate('MBHBaseBundle:Mailer:packageMoving.html.twig')
            ->setAdditionalData([
                'movingInfo' => $packageMovingInfo,
            ])
            ->setAutohide(false)
            ->setEnd(new \DateTime('+1 minute'));

        $this->mailer
            ->setMessage($message)
            ->notify();
    }
}