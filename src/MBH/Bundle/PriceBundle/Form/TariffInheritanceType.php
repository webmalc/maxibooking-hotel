<?php

namespace MBH\Bundle\PriceBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TariffInheritanceType

 */
class TariffInheritanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $group = 'Наследование от тарифа <' . $options['parent'] . '>';

        $builder
            ->add('inheritPrices', CheckboxType::class, [
                'label' => 'Цены',
                'group' => $group,
                'required' => false,
                'help' => 'Наследовать ли цены от родительского тарифа'
            ])
            ->add('inheritRestrictions', CheckboxType::class, [
                'label' => 'Условия и ограничения',
                'group' => $group,
                'required' => false,
                'help' => 'Наследовать ли условия и ограничения от родительского тарифа'
            ])
            ->add('inheritRooms', CheckboxType::class, [
                'label' => 'Квоты',
                'group' => $group,
                'required' => false,
                'help' => 'Наследовать ли квоты номеров от родительского тариф'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\TariffChildOptions',
            'parent' => null
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_child_options';
    }

}