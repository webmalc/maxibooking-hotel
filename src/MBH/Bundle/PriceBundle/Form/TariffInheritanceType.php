<?php

namespace MBH\Bundle\PriceBundle\Form;


use Symfony\Component\Form\AbstractType;
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
            ->add('inheritPrices', 'checkbox', [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.tseny',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹlitsenyotroditelʹskogotarifa'
            ])
            ->add('inheritRestrictions', 'checkbox', [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.usloviyaiogranicheniya',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹliusloviyaiogranicheniyaotroditelʹskogotarifa'
            ])
            ->add('inheritRooms', 'checkbox', [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.kvoty',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹlikvotynomerovotroditelʹskogotarif'
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


    public function getName()
    {
        return 'mbh_price_tariff_child_options';
    }

}