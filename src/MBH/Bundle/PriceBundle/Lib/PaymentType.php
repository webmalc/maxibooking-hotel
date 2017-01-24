<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/31/16
 * Time: 5:32 PM
 */

namespace MBH\Bundle\PriceBundle\Lib;

/** TODO: Сделать по-человечески. Документом с привязкой к отелям */
class PaymentType
{
    const PAYMENT_TYPE_LIST = [
        'ten' => [
            'name' => '',
            'description' => 'Десять процентов',
            'value' => '10'
        ],
        'twenty' => [
            'name' => '',
            'description' => 'Двадцать процентов',
            'value' => '20'
        ],
        'thirty' => [
            'name' => '',
            'description' => 'Тридцать процентов',
            'value' => '30'
        ],
        'forty' => [
            'name' => '',
            'description' => 'Сорок процентов',
            'value' => '40'
        ],
        'fifty' => [
            'name' => '',
            'description' => 'Пятьдесят процентов',
            'value' => '50'
        ],
        'sixty' => [
            'name' => '',
            'description' => 'Шестьдесят процентов',
            'value' => '60'
        ],
        'seventy' => [
            'name' => '',
            'description' => 'Семьдесят процентов',
            'value' => '70'
        ],
        'eighty' => [
            'name' => '',
            'description' => 'Восемьдесят процентов',
            'value' => '80'
        ],
        'ninety' => [
            'name' => '',
            'description' => 'Девяносто процентов',
            'value' => '90'
        ],
        'hundred' => [
            'name' => '',
            'description' => 'Сто процентов',
            'value' => '100'

        ]
    ];

    public static function getPercentChoices()
    {
        return self::PAYMENT_TYPE_LIST;
    }
    //Для колбека
    public static function getPaymentPercentValues()
    {
        return array_keys(self::PAYMENT_TYPE_LIST);
    }
}