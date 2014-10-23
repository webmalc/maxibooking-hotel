<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;

class RoomQuotaType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tariff = $options['entity'];
        $roomTypes = $tariff->getHotel()->getRoomTypes();
        $quotasEntities = $tariff->getRoomQuotas();
        $quotas = [];
        
        foreach ($quotasEntities as $quota) {
            $quotas[$quota->getRoomType()->getId()] = $quota->getNumber();
        }

        foreach ($roomTypes as $roomType) {
            
            (isset($quotas[$roomType->getId()])) ? $data = $quotas[$roomType->getId()] : $data = null;

            if ($roomType->getHotel()->getIsHostel() && $roomType->getCalculationType() == 'customPrices') {
                $title = 'койко-места';
            } else {
                $title = 'номера';
            }

            $builder
                    ->add($roomType->getId(), 'text', [
                        'label' => $roomType->getName(),
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Используются все ' . $title . ': ' . $roomType->getRooms()->count(),
                            'class' => 'spinner quota-spinner'
                        ],
                        'data' => $data,
                        'constraints' => new Range(['min' => 0, 'minMessage' => 'Квота не может быть меньше нуля'])
                    ])
            ;
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'entity' => false
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_room_quota_type';
    }

}
