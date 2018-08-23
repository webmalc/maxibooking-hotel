<?php

namespace MBH\Bundle\HotelBundle\Form\RoomTypeFlow;

use MBH\Bundle\HotelBundle\Service\FormFlow;
use Symfony\Component\Form\FormInterface;

class RoomTypeFlow extends FormFlow
{
    protected function getStepsConfig(): array
    {
        return [
            [
                'label' => 'Тип номера',
                'form_type' => RoomTypeFlowType::class
            ],
            [
                'label' => 'Информация о номере',
                'form_type' => RoomTypeFlowType::class
            ],
            [
                'label' => 'Фотографии'
            ],
            [
                'label' => 'Количество мест'
            ],
            [
                'label' => 'Тип цен'
            ],
            [
                'label' => 'Период'
            ],
            [
                'label' => 'Цена'
            ]
        ];
    }

    protected function getFormData()
    {
        // TODO: Implement getFormData() method.
    }

    public function handleStep(FormInterface $form)
    {
        // TODO: Implement handleForm() method.
    }
}