<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TariffPromotionsType

 */
class TariffPromotionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('promotions', 'document', [
                'label' => 'mbhpricebundle.form.tariffpromotionstype.dostupnyyeaktsii',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.tariffpromotionstype.osnovnoy'],
                'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                'multiple' => true,
            ])
            ->add('defaultPromotion', 'document', [
                'label' => 'mbhpricebundle.form.tariffpromotionstype.aktsiipoumolchaniyu',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.tariffpromotionstype.osnovnoy'],
                'class' => 'MBH\Bundle\PriceBundle\Document\Promotion'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff'
        ]);
    }


    public function getName()
    {
        return 'mbh_price_tariff_promotions';
    }

}
