<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class RoomPriceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tariff = $options['entity'];
        $roomTypes = $tariff->getHotel()->getRoomTypes();
        $pricesEntities = $tariff->getRoomPrices();
        $prices = [];
        
        ($tariff->getIsDefault()) ? $placeholder = 'Цена не указана' : $placeholder = 'Цена основного тарифа';
        
        foreach ($pricesEntities as $price) {
            $prices[$price->getRoomType()->getId()  . '_price'] = $price->getPrice();
            $prices[$price->getRoomType()->getId()  . '_additionalAdultPrice'] = $price->getAdditionalAdultPrice();
            $prices[$price->getRoomType()->getId()  . '_additionalChildPrice'] = $price->getAdditionalChildPrice();
        }

        foreach ($roomTypes as $roomType) {

            if ($roomType->getHotel()->getIsHostel() && $roomType->getCalculationType() == 'customPrices') {
                $attr = [
                    'disabled' => 'disabled',
                    'placeholder' => $placeholder,
                    'class' => 'spinner price-spinner'
                ];
            } else {
                $attr = [
                    'placeholder' => $placeholder,
                    'class' => 'spinner price-spinner'
                ];
            }

            $builder
                    ->add($roomType->getId() . '_price', 'text', [
                        'label' => 'Основная цена',
                        'group' => $roomType->getName(),
                        'required' => false,
                        'attr' => [
                            'placeholder' => $placeholder,
                            'class' => 'spinner price-spinner'
                        ],
                        'data' => (isset($prices[$roomType->getId() . '_price']) ? $prices[$roomType->getId() . '_price'] : null),
                        'constraints' => new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                    ])
                    ->add($roomType->getId() . '_additionalAdultPrice', 'text', [
                        'label' => 'Доп. место ребенок',
                        'group' => $roomType->getName(),
                        'required' => false,
                        'attr' => $attr,
                        'data' => (isset($prices[$roomType->getId() . '_additionalAdultPrice']) ? $prices[$roomType->getId() . '_additionalAdultPrice'] : null),
                        'constraints' => new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                    ])
                    ->add($roomType->getId() . '_additionalChildPrice', 'text', [
                        'label' => 'Доп. место взрослый',
                        'group' => $roomType->getName(),
                        'required' => false,
                        'attr' => $attr,
                        'data' => (isset($prices[$roomType->getId() . '_additionalChildPrice']) ? $prices[$roomType->getId() . '_additionalChildPrice'] : null),
                        'constraints' => new Range(['min' => 0, 'minMessage' => 'Цена не может быть меньше нуля'])
                    ])
            ;
        }
    }
   
    public static function parseData(array $data)
    {
        $result = [];
        
        foreach($data as $key => $value) {
            
            if($value === null) {
                continue;
            }
            $fieldParams = explode('_', $key);
            $result[$fieldParams[0]][$fieldParams[1]] = $value;
        }
        
        return $result;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => false
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_room_price_type';
    }

}
