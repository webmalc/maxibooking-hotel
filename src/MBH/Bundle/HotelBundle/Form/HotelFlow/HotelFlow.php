<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use MBH\Bundle\HotelBundle\Form\HotelImageType;
use MBH\Bundle\HotelBundle\Service\FormFlow;

class HotelFlow extends FormFlow
{
    protected $allowRedirectAfterSubmit = true;

    public function isAddImageButtonClick()
    {
        return $this->isButtonClicked('add_image');
    }

    /**
     * @return array
     */
    protected function getStepsConfig()
    {
        return [
            [
                'label' => 'Ввод имени отеля',
                'form_type' => HotelFlowType::class,
            ],
            [
                'label' => 'Ввод описания отеля',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Логотип отеля',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Адрес отеля',
                'form_type' => HotelAddressType::class
            ],
            [
                'label' => 'Координаты отеля на карте',
                'form_type' => HotelLocationType::class
            ],
            [
                'label' => 'Контакты',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Главная фотография',
                'form_type' => HotelFlowType::class
            ],
            [
                'label' => 'Фотографии',
                'form_type' => HotelImageType::class,
                'options' => [
                    'withIsDefaultField' => false,
                    'hasConstraints' => false
                ]
            ]
        ];
    }
}