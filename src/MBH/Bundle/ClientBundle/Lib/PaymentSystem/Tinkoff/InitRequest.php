<?php
/**
 * Created by PhpStorm.
 * Date: 01.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Tinkoff;


use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\Tinkoff;
use MBH\Bundle\PackageBundle\Document\Order;

class InitRequest extends InitCommon implements \JsonSerializable
{
    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * IP-адрес клиента
     * обязательный нет
     * String(40)
     *
     * @var null
     */
    private $ip;

    /**
     * Краткое описание
     * обязательный нет
     * String(250)
     *
     * @var null|string
     */
    private $description;

    /**
     * Подпись запроса. Алгоритм формирования подписи описан в разделе "Подпись запросов"
     * обязательный нет
     *
     * @var null|string
     */
    private $token;

    /**
     * Язык платёжной формы.
     *      ru - форма оплаты на русском языке;
     *      en - форма оплаты на английском языке.
     * По умолчанию (если параметр не передан) - форма оплаты на русском языке
     * обязательный нет
     * String(2)
     *
     * @var null|string
     */
    private $language;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Идентификатор покупателя в системе Продавца.
     * Если передается,
     * для данного покупателя будет осуществлена привязка карты к данному идентификатору клиента CustomerKey.
     * В нотификации на AUTHORIZED будет передан параметр CardId, подробнее см. метод GetGardList.
     * Параметр обязателен, если Recurrent = Y
     * обязательный нет
     * String(36)
     *
     * @var null
     */
    private $customerKey;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Если передается и установлен в Y, регистрирует платёж как рекуррентный.
     * В этом случае после оплаты в нотификации на AUTHORIZED будет передан параметр RebillId для использования в методе Charge
     * обязательный нет
     * String(1)
     *
     * @var null
     */
    private $recurrent;

    /**
     * Cрок жизни ссылки.
     * В случае, если текущая дата превышает дату переданную в данном параметре,
     * ссылка для оплаты становится недоступной и платёж выполнить нельзя.
     *      Формат даты: YYYY-MM-DDTHH24:MI:SS+GMT
     *      Пример даты: 2016-08-31T12:28:00+03:00
     * обязательный нет
     *
     * Datetime
     *
     * @var null|\DateTime
     */
    private $redirectDueDate;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * JSON объект, содержащий дополнительные параметры в виде “ключ”:”значение”.
     * Данные параметры будут переданы на страницу оплаты (в случае ее кастомизации).
     * Максимальная длина для каждого передаваемого параметра:
     *      Ключ – 20 знаков,
     *      Значение – 100 знаков.
     *      Максимальное количество пар «ключ-значение» не может превышать 20
     * обязательный нет
     * Object
     *
     * @var null
     */
    private $data;

    /**
     * JSON объект с данными чека
     * обязательный нет
     * Object
     *
     * @var null|Receipt
     */
    private $receipt;

    /**
     * Для генерации ключа
     *
     * @var string
     */
    private $password;

    public static function create(CashDocument $cashDocument, Tinkoff $tinkoff): self
    {
        $self = new self();
        $self->setOrderId($cashDocument->getId());
        $self->setDescription($cashDocument);
        $self->setAmount($cashDocument->getTotal() * 100);
        $self->setLanguage($tinkoff->getLanguage());
        $self->setRedirectDueDate($tinkoff->getRedirectDueDate());
        $self->setTerminalKey($tinkoff->getTerminalKey());
        $self->setPassword($tinkoff->getSecretKey());

        if ($tinkoff->isWithFiscalization()) {
            $self->setReceipt(Receipt::create($cashDocument, $tinkoff));
        }

        return $self;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $terminalKey
     */
    public function setTerminalKey(string $terminalKey): void
    {
        $this->terminalKey = $terminalKey;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(CashDocument $cashDocument): void
    {
        $hotel = $cashDocument->getHotel();
        // т.к. генерация происходит из онлайн формы, те ордер новый и пакедж только один
        $package = $cashDocument->getOrder()->getPackages()[0];

        $desc = 'Проживание в "%1$s" c %2$s по %3$s.';

        $this->description =
            sprintf(
                $desc,
                $hotel,
                $package->getBegin()->format('d.m.Y'),
                $package->getEnd()->format('d.m.Y')
                );
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        $param = [
            'OrderId'     => $this->getOrderId(),
            'Amount'      => $this->getAmount(),
            'TerminalKey' => $this->getTerminalKey(),
            'Language'    => $this->getLanguage(),
            'Password'    => $this->password,
        ];

        return $this->returnSha256($param);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param null|string $language
     */
    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getRedirectDueDate(): string
    {
        return $this->redirectDueDate;
    }

    /**
     * @param int $redirectDueDate
     */
    public function setRedirectDueDate(int $redirectDueDate): void
    {
        $date = new \DateTime('+'. $redirectDueDate . ' hour');
        $this->redirectDueDate = $date->format(DATE_ATOM);
    }

    /**
     * @return Receipt|null
     */
    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }

    /**
     * @param Receipt|null $receipt
     */
    public function setReceipt(?Receipt $receipt): void
    {
        $this->receipt = $receipt;
    }

    public function jsonSerialize()
    {
        $data = [
            'TerminalKey' => $this->getTerminalKey(),
            'Amount' => $this->getAmount(),
            'OrderId' => $this->getOrderId(),
            'Description' => $this->getDescription(),
            'Token' => $this->getToken(),
            'Language' => $this->getLanguage(),
            'RedirectDueDate' => $this->getRedirectDueDate(),
        ];

        if ($this->getReceipt() !== null) {
            $data['Receipt'] = $this->getReceipt();
        }

        return $data;
    }
}