<?php
/**
 * Created by PhpStorm.
 * Date: 03.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;

use MBH\Bundle\ClientBundle\Document\PaymentSystem\Tinkoff;
use Symfony\Component\HttpFoundation\Request;

/**
 * @see https://oplata.tinkoff.ru/landing/develop/notifications/http
 *
 * Class Notification
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff
 */
class Notification extends Response
{
    public const STATUS_AUTHORIZED = 'AUTHORIZED';              //	Деньги захолдированы на карте клиента. Ожидается подтверждение операции*
    public const STATUS_CONFIRMED = 'CONFIRMED';                // Операция подтверждена
    public const STATUS_REVERSED = 'REVERSED';                  //	Операция отменена
    public const STATUS_REFUNDED = 'REFUNDED';                  //	Произведён возврат
    public const STATUS_PARTIAL_REFUNDED = 'PARTIAL_REFUNDED';  //	Произведён частичный возврат
    public const STATUS_REJECTED = 'REJECTED';                  //	Списание денежных средств закончилась ошибкой

    /**
     * Идентификатор рекуррентного платежа
     * Number
     *
     * @var null|int
     */
    private $rebillId;

    /**
     * Идентификатор привязанной карты
     * Number
     *
     * @var int
     */
    private $cardId;

    /**
     * Маскированный номер карты
     * String
     *
     * @var string
     */
    private $pan;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ (от тинькова приходит DATA)
     *
     * Дополнительные параметры платежа, переданные при создании заказа
     * String
     *
     * @var string
     */
    private $data;

    /**
     * Подпись запроса. Алгоритм формирования подписи описан в разделе "Проверка токенов"
     * String
     *
     * @var string
     */
    private $token;

    /**
     * Срок действия карты
     * String
     *
     * @var string
     */
    private $expDate;

    /**
     * Номер чека в смене. Целочисленное значение
     * Number
     *
     * @var null|int
     */
    private $fiscalNumber;

    /**
     * Номер смены. Целочисленное значение
     * Number
     *
     * @var null|int
     */
    private $shiftNumber;

    /**
     * Дата и время документа из ФН
     * Date
     *
     * @var null|string
     */
    private $receiptDatetime;

    /**
     * Номер ФН
     * String(20)
     *
     * @var null|string
     */
    private $fnNumber;

    /**
     * Регистрационный номер ККТ
     * String(20)
     *
     * @var null|string
     */
    private $ecrRegNumber;

    /**
     * Фискальный номер документа. Целочисленное значение
     * Number
     *
     * @var null|integer
     */
    private $fiscalDocumentNumber;

    /**
     * Фискальный признак документа. Целочисленное значение
     * Number
     *
     * @var null|integer
     */
    private $fiscalDocumentAttribute;

    /**
     * Наименование оператора фискальных данных. Только для онлайн-касс Cloud Kassir
     * String
     *
     * @var null|string
     */
    private $ofd;

    /**
     * URL адрес с копией чека. Только для онлайн-касс Cloud Kassir
     * String
     *
     * @var null|string
     */
    private $url;

    /**
     * URL адрес с QR кодом для проверки чека в ФН Только для онлайн-касс Cloud Kassir
     * String
     *
     * @var null|string
     */
    private $qrCodeUrl;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Данные чека. Повторяет структуру объекта Receipt для инициализации платежа при вызове метода Init
     * Object
     *
     * @var
     */
    private $receipt;

    /**
     * Тип чека, признак расчета:
     *      Income (Приход) — выдается при получении средств от покупателя (клиента). Метод Init, Confirm, Charge.
     *      IncomeReturn (Возврат прихода) — Выдается при возврате покупателю (клиенту) средств, полученных от него. Метод Cancel
     * String
     *
     * @var null|string
     */
    private $type;

    public static function parseRequest(Request $request): ?self
    {
        $body = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($body)) {
            return null;
        }

        $self = new self();
        foreach ($body as $key => $value) {
            $key = lcfirst($key);
            if (property_exists(self::class, $key)) {
                $self->$key = $value;
            }
        }

        return $self;
    }

    /**
     * @param Tinkoff $tinkoff
     * @return bool
     */
    public function compareToken(Tinkoff $tinkoff): bool
    {
        $data = [
            'TerminalKey' => $tinkoff->getTerminalKey(),
            'OrderId'     => $this->getOrderId(),
            'Success'     => $this->getSuccess(),
            'Status'      => $this->getStatus(),
            'PaymentId'   => $this->getPaymentId(),
            'ErrorCode'   => $this->getErrorCode(),
            'Amount'      => $this->getAmount(),
            'Password'    => $tinkoff->getSecretKey(),
        ];

        if ($tinkoff->isWithFiscalization()) {
            $data = array_merge(
                $data,
                [
                    'FiscalNumber'            => $this->getFiscalNumber(),
                    'ShiftNumber'             => $this->getShiftNumber(),
                    'ReceiptDatetime'         => $this->getReceiptDatetime(),
                    'FnNumber'                => $this->getFnNumber(),
                    'EcrRegNumber'            => $this->getEcrRegNumber(),
                    'FiscalDocumentNumber'    => $this->getFiscalDocumentNumber(),
                    'FiscalDocumentAttribute' => $this->getFiscalDocumentAttribute(),
                    'Ofd'                     => $this->getOfd(),
                    'Url'                     => $this->getUrl(),
                    'QrCodeUrl'               => $this->getQrCodeUrl(),
                    'Type'                    => $this->getType(),
                ]
            );

        } else {
            $data = array_merge(
                $data,
                [
                    'CardId'   => $this->getCardId(),
                    'Pan'      => $this->getPan(),
                    'ExpDate'  => $this->getExpDate(),
                    'RebillId' => $this->getRebillId(),
                ]
            );
        }

        return $this->returnSha256($data) === $this->getToken();
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return null|int
     */
    public function getRebillId(): ?int
    {
        return $this->rebillId;
    }

    /**
     * @return int
     */
    public function getCardId(): int
    {
        return $this->cardId;
    }

    /**
     * @return string
     */
    public function getPan(): string
    {
        return $this->pan;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getExpDate(): string
    {
        return $this->expDate;
    }

    /**
     * @return int|null
     */
    public function getFiscalNumber(): ?int
    {
        return $this->fiscalNumber;
    }

    /**
     * @return int|null
     */
    public function getShiftNumber(): ?int
    {
        return $this->shiftNumber;
    }

    /**
     * @return null|string
     */
    public function getReceiptDatetime(): ?string
    {
        return $this->receiptDatetime;
    }

    /**
     * @return null|string
     */
    public function getFnNumber(): ?string
    {
        return $this->fnNumber;
    }

    /**
     * @return null|string
     */
    public function getEcrRegNumber(): ?string
    {
        return $this->ecrRegNumber;
    }

    /**
     * @return int|null
     */
    public function getFiscalDocumentNumber(): ?int
    {
        return $this->fiscalDocumentNumber;
    }

    /**
     * @return int|null
     */
    public function getFiscalDocumentAttribute(): ?int
    {
        return $this->fiscalDocumentAttribute;
    }

    /**
     * @return null|string
     */
    public function getOfd(): ?string
    {
        return $this->ofd;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return null|string
     */
    public function getQrCodeUrl(): ?string
    {
        return $this->qrCodeUrl;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}