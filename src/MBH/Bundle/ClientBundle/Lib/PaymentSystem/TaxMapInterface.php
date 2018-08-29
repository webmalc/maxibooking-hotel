<?php
/**
 * Created by PhpStorm.
 * Date: 02.07.18
 */

namespace MBH\Bundle\ClientBundle\Lib\PaymentSystem;

/**
 * Методы возвращают массивы соответствия, где:
 * ключ - это ключи Uniteller (она была первой)
 * значения - это ключи выбранной платежной системы
 *
 * rate_codes:
 *   -1: не облагается НДС
 *   0: облагается НДС по ставке 0%
 *   10: облагается НДС по ставке 10%
 *   18: облагается НДС по ставке 18%
 *   110: облагается НДС по ставке 10/110
 *   118: облагается НДС по ставке 18/118
 * system_codes:
 *   0: Общая система налогообложения
 *   1: Упрощенная система налогообложения (Доход)
 *   2: Упрощенная СН (Доход минус Расход)
 *   3: Единый налог на вмененный доход
 *   4: Единый сельскохозяйственный налог
 *   5: Патентная система налогообложения
 *
 * Interface TaxMapInterface
 * @package MBH\Bundle\ClientBundle\Lib\PaymentSystem
 */
interface TaxMapInterface
{
    /**
     * @return array
     */
    public function getTaxSystemMap(): array;

    /**
     * @return array
     */
    public function getTaxRateMap(): array;
}