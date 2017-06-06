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
                'label' => 'mbhpricebundle.form.tariffinheritancetype.tseny',
                'group' => $group,
                'required' => false,
                'help' => 'Наследовать ли цены от родительского тарифа'
            ])
            ->add('inheritRestrictions', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.usloviya.i.ogranicheniya',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹ.li.usloviya.i.ogranicheniya.ot.roditelʹskogo.tarifa'
            ])
            ->add('inheritRooms', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.kvoty',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹ.li.kvoty.nomerov.ot.roditelʹskogo.tarif'
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