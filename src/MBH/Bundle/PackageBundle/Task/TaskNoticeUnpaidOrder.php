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

    /**
     * Array is unpaid orders
     *
     * @var NoticeUnpaid
     */
    private $unpaidOrder;

    public function __construct(NoticeUnpaid $noticeUnpaid)
    {
        $this->noticeUnpaid = $noticeUnpaid;
        $this->unpaidOrder = $noticeUnpaid->unpaidOrder();
    }

    public function execute(AMQPMessage $message)
    {
        $this->noticeUnpaid->sendNotice($this->unpaidOrder);
    }
}