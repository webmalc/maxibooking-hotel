<?php
/**
 * Created by PhpStorm.
 * Date: 03.09.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

use MBH\Bundle\ClientBundle\Document\PaymentSystem\Sberbank;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CallbackNotification
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:callback:start
 */
class CallbackNotification
{
    public const OPERATION_APPROVED = 'approved';  //операция удержания (холдирования) суммы
    public const OPERATION_DEPOSITED = 'deposited'; //операция завершения;
    public const OPERATION_REVERSED = 'reversed';  //операция отмены;
    public const OPERATION_REFUNDED = 'refunded';  //операция возврата.

    public const STATUS_SUCCESS = 1; //операция прошла успешно
    public const STATUS_FAIL = 0;    //операция завершилась ошибкой

    /**
     * @var string
     */
    private $mdOrder;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $checksum;

    /**
     * @var string
     */
    private $operation;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var array
     */
    private $extraParams = [];

    /**
     * @param Request $request
     * @return CallbackNotification|null
     */
    public static function parseRequest(Request $request): ?self
    {
        $body = $request->query->getIterator();

        if (json_last_error() !== JSON_ERROR_NONE || count($body)=== 0) {
            return null;
        }
        $self = new self();
        foreach ($body as $key => $value) {
            $key = lcfirst($key);
            if (property_exists(self::class, $key)) {
                $self->$key = $value;
            } else {
                $self->extraParams[$key] = $value;
            }
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getMdOrder(): string
    {
        return $this->mdOrder;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function generateRawString(): string
    {
        $data = [];

        $data['mdOrder'] = $this->getMdOrder();
        $data['orderNumber'] = $this->getOrderNumber();
        $data['operation'] = $this->getOperation();
        $data['status'] = (string)$this->getStatus();

        if ($this->extraParams !== []) {
            $data = array_merge($data, $this->extraParams);
        }

        ksort($data);

        $d = [];
        foreach ($data as $key => $value) {
            $d[] = $key . ';' . $value;
        }

        return implode(';', $d) . ';';
    }

    /**
     * @param Sberbank $sberbank
     * @return string
     */
    public function generateHmacSha256(Sberbank $sberbank): string
    {
        return strtoupper(hash_hmac('sha256', $this->generateRawString(), $sberbank->getSecurityKey()));
    }

}