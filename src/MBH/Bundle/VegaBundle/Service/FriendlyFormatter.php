<?php

namespace MBH\Bundle\VegaBundle\Service;

/**
 * Class FriendlyFormatter
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class FriendlyFormatter
{
    const CHARSET = "UTF-8";

    public static function convertCountry($country)
    {
        $specialNames = [
            'США', 'СССР', 'КНДР', 'ЮАР', 'ГДР', 'ЧССР'
        ];
        if (in_array($country, $specialNames)) {
            return $country;
        }

        $result = str_replace(' И ', ' и ', $country);
        $result = mb_convert_case($result, MB_CASE_TITLE, self::CHARSET);

        return $result;
    }

    public static function convertCitizen($citizen)
    {
        $result = mb_convert_case($citizen, MB_CASE_TITLE, self::CHARSET);
        return $result;
    }

    public static function convertRegion($region)
    {
        $result = mb_strtolower($region);
        $result = mb_convert_case($result, MB_CASE_TITLE, self::CHARSET);
        $ends = mb_substr($result, -2);

        $specialNames = [
            'Краснодарский' => 'край',
            'Ханты-Мансийский Автономный Округ - Югра',
            'Карелия' => 'респ.',
            'Приморский' => 'край',
            'Северная Осетия - Алания',
            'Башкортостан' => 'респ.',
            'Коми' => 'респ.',
            'Калмыкия' => 'респ.',
            'Татарстан' => 'респ.',
            'Ямало-Ненецкий' => 'авт. округ',
            'Москва',
            'Пермский' => 'край',
            'Дагестан' => 'респ.',
            'Байконур' => '',
            'Саха /якутия/' => 'респ.',
            'Бурятия' => 'респ.',
            'Ингушетия' => 'респ.',
            'Адыгея' => 'респ.',
            'Хакасия' => 'респ.',
            'Хабаровский' => 'край',
            'Забайкальский' => 'край',
            'Ставропольский' => 'край',
            'Марий Эл' => 'респ.',
            'Ненецкий' => 'авт. округ',
            'Тыва' => 'респ.',
            'Мордовия' => 'респ.',
            'Алтайский' => 'край',
            'Чувашская Республика -',
            'Чукотский' => 'авт. округ',
            'Красноярский' => 'край',
            'Санкт-Петербург',
            'Севастополь',
            'Алтай' => 'респ.',
            'Крым' => 'респ.',
        ];

        if ($ends == 'ая') {
            $result .= ' обл.';
        } elseif (!in_array($result, $specialNames)) {
            $type = $specialNames[$result];
            if($type == 'респ.') {
                $result = $type.' '.$result;
            }else {
                $result .= ' '.$type;
            }
        }

        return $result;
    }

    /**
     * @param string $fms
     * @return string
     */
    public static function convertFMS($fms)
    {
        $result = mb_convert_case($fms, MB_CASE_TITLE, self::CHARSET);

        $search = [
            'Овд', 'Уфмс', 'Офмс', 'Оуфмс', 'Ровд', 'Говд', 'Ом', 'Гом', 'Оик', 'Тп',
            'Увд', 'Уao', 'Cao', 'Зао', 'Сзао', 'Юао', 'Вао', 'Свао', 'Юзао', 'Ювао'
        ];
        $replace = array_map('mb_strtoupper', $search);

        $search = array_merge($search, [' Г. ','Обл.', 'Р-на', 'Р-На', 'Р-не', 'Р-Не', ' По ', ' В ', ' На ', ' Гор. ']);
        $replace = array_merge($replace, [' г. ', 'обл.','р-на', 'р-на','р-не', 'р-не', ' по ', ' в ', ' на ', ' гор. ']);

        //var_dump(array_combine($search, $replace));die();
        $result = str_replace($search, $replace, $result);

        return $result;
    }

    /**
     * @param string $name
     * @return string
     */
    public static function convertDocumentType($name)
    {
        $result = mb_convert_case($name, MB_CASE_TITLE, self::CHARSET);

        $search = array_merge([' По ', ' В ', ' На ']);
        $replace = array_merge([' по ', ' в ', ' на ']);

        $result = str_replace($search, $replace, $result);
        return $result;
    }
}