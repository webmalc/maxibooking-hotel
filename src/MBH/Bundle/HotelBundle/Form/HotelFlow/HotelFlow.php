<?php

namespace MBH\Bundle\HotelBundle\Form\HotelFlow;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Tests\IntegrationTestBundle\Form\PhotoUploadForm;

class HotelFlow extends FormFlow
{
    /**
     * @return array
     */
    protected function loadStepsConfig()
    {
        $hotelFlowLabels = [
            'Ввод имени отеля',
            'Ввод описания отеля',
            'Логотип отеля',
            'Адрес отеля',
            'Координаты отеля на карте',
            'Контакты'
        ];

        $steps = array_map(function (string $step) {
            return [
                'label' => $step,
                'form_type' => HotelFlowType::class
            ];
        }, $hotelFlowLabels);

        return array_merge($steps, [[
            'label' => 'Фотографии',
            'form_type' => PhotoUploadForm::class
            ]
        ]);
    }
}