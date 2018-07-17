<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\HotelBundle\Service\FormFlow;

class HotelFlow extends FormFlow
{
    protected $allowRedirectAfterSubmit = true;

    /**
     * @return array
     */
    protected function getStepsConfig()
    {
        return [
            [
                'Ввод имени отеля',
                'form_type' => HotelFlowType::class,
            ],
            [
                'Ввод описания отеля',
                'form_type' => HotelFlowType::class
            ],
            [
                'Логотип отеля',
                'form_type' => HotelFlowType::class
            ],
            [
                'Адрес отеля',
                'form_type' => HotelAddressType::class
            ],
            [
                'Координаты отеля на карте',
                'form_type' => HotelLocationType::class
            ],
            [
                'Контакты',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Главная фотография',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Фотографии',
                'form_type' => HotelFlowType::class
            ]
        ];
    }
}