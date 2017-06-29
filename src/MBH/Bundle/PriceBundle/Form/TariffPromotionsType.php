<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
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
            ->add('promotions', DocumentType::class, [
                'label' => 'mbhpricebundle.form.tariffpromotionstype.dostupnyyeaktsii',
                'group' => 'mbhpricebundle.form.tarifftype.promotions.common_info.group_name',
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.tariffpromotionstype.osnovnoy'],
                'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                'multiple' => true,
            ])
            ->add('defaultPromotion', DocumentType::class, [
                'label' => 'mbhpricebundle.form.tariffpromotionstype.aktsiipoumolchaniyu',
                'group' => 'mbhpricebundle.form.tarifftype.promotions.common_info.group_name',
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


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_promotions';
    }

}
