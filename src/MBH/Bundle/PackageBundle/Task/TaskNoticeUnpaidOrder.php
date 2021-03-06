<?php

namespace MBH\Bundle\PackageBundle\Task;

use MBH\Bundle\ClientBundle\Service\NoticeUnpaid;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use MBH\Bundle\BaseBundle\Lib\Task\LoggerTrait;

class TaskNoticeUnpaidOrder implements ConsumerInterface
{
    use LoggerTrait;

    /**
     * Notice Unpaid Service
     *
     * @var NoticeUnpaid
     */
    private $noticeUnpaid;

    public function __construct(NoticeUnpaid $noticeUnpaid)
    {
        $this->noticeUnpaid = $noticeUnpaid;
    }

    public function execute(AMQPMessage $message)
    {
        $this->noticeUnpaid->sendNotice();
    }
}