<?php


namespace MBH\Bundle\BaseBundle\Lib;


class MessageTypes
{
    public const ONLINE_ORDER = 'online_order';
    public const CHANNEL_MANAGER = 'channel_manager_order';
    public const CASH_DOC_CONFIRMATION = 'cash_confirm';
    public const ONLINE_PAYMENT_CONFIRM = 'online_payment_confirm';
    public const ARRIVAL = 'arrival';
    public const FEEDBACK = 'feedback';
    public const UNPAID = 'unpaid';
    public const CONFIRM_ORDER = 'confirm_order';
    public const TASK = 'task';

    public const STUFF_GROUP = [
        self::ARRIVAL,
        self::CHANNEL_MANAGER,
        self::ONLINE_ORDER,
        self::ONLINE_PAYMENT_CONFIRM,
        self::TASK,
        self::UNPAID
    ];

    public const CLIENT_GROUP = [
        self::ARRIVAL,
        self::FEEDBACK,
        self::ONLINE_PAYMENT_CONFIRM,
        self::CASH_DOC_CONFIRMATION,
        self::ONLINE_ORDER,
        self::CONFIRM_ORDER
    ];

    public static function getClientOptionsList(): ?array
    {
        return static::CLIENT_GROUP;
    }

    public static function getStuffOptionsList(): ?array
    {
        return static::STUFF_GROUP;
    }
}