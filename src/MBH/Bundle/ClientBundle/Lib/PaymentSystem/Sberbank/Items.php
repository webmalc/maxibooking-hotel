<?php
/**
 * Created by PhpStorm.
 * Date: 30.08.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank;

/**
 * Class Items
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem\Sberbank
 *
 * @see https://securepayments.sberbank.ru/wiki/doku.php/integration:api:rest:requests:register_cart#items
 */
class Items
{
    /**
     * Уникальный идентификатор товарной позиции внутри корзины заказа.
     *
     * @var integer
     */
    private $positionId;

    /**
     * Наименование или описание товарной позиции в свободной форме.
     *
     * @var string
     */
    private $name;
    
    /**
     * Дополнительный блок с параметрами описания товарной позиции.
     *
     * @var 
     */
    private $itemDetails;

    /**
     * Элемент описывающий общее количество товарных позиций одного positionId и их меру измерения
     *
     * @var
     */
    private $quantity;

    /**
     * Сумма стоимости всех товарных позиций одного positionId в минимальных единицах валюты.
     * itemAmount обязателен к передаче, только если не был передан параметр itemPrice.
     * В противном случае передача itemAmount не требуется.
     * Если же в запросе передаются оба параметра: itemPrice и itemAmount,
     * то itemAmount должен равняться itemPrice * quantity, в противном случае запрос завершится с ошибкой
     *
     * @var integer
     */
    private $itemAmount;

    /**
     * Код валюты товарной позиции ISO 4217. Если не указан, считается равным валюте заказа
     *
     * @var integer
     */
    private $itemCurrency;

    /**
     * Номер (идентификатор) товарной позиции в системе магазина.
     *
     * @var string
     */
    private $itemCode;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Дополнительный блок с атрибутами описания скидки для товарной позиции
     *
     * @var
     */
    private $discount;

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ
     *
     * Дополнительный блок с атрибутами описания агентской комиссии за продажу товара.
     *
     * @var
     */
    private $agentInterest;

    /**
     * Дополнительный тег с атрибутами описания налога
     *
     * @var int
     */
    private $tax;

    /**
     * Стоимость одной товарной позиции одного positionId в минимальных единицах валюты (например, в копейках).
     *
     * @var int
     */
    private $itemPrice;
}