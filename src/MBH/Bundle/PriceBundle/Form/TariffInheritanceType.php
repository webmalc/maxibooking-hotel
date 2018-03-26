<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TariffInheritanceType
 */
class TariffInheritanceType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $group = $this->translator->trans('mbhpricebundle.form.tariff_inheritance_type.group.inheritance') . ' <' . $options['parent'] . '>';

        $builder
            ->add('inheritPrices', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.tseny',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.tseny.help'
            ])
            ->add('inheritRestrictions', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.tariffinheritancetype.usloviyaiogranicheniya',
                'group' => $group,
                'required' => false,
                'help' => 'mbhpricebundle.form.tariffinheritancetype.nasledovatʹliusloviyaiogranicheniyaotroditelʹskogotarifa'
            ])
            ->add('inheritRooms', CheckboxType::class, [
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


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_child_options';
    }

}