<?php

namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TariffServicesType

 */
class TariffServicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('services', DocumentType::class, [
                'label' => 'mbhpricebundle.form.tariffservicestype.dostupnyye.uslugi',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['data-placeholder' => 'mbhpricebundle.form.tariffservicestype.vse.uslugi'],
                'class' => 'MBH\Bundle\PriceBundle\Document\Service',
                'choices' => $options['services_all'],
                'multiple' => true
            ])
            ->add('defaultServices', CollectionType::class, [
                'label' => 'mbhpricebundle.form.tariffservicestype.uslugi.po.umolchaniyu',
                'group' => 'Общая информация',
                'required' => false,
                'entry_type' => TariffServiceType::class,
                'entry_options' => ['services' => $options['services']],
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
            'services' => [],
            'services_all' => []
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_promotions';
    }

}