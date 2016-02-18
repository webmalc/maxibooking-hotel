<?php

namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
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
            ->add('services', 'document', [
                'label' => 'Доступные услуги',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['data-placeholder' => 'Все услуги'],
                'class' => 'MBH\Bundle\PriceBundle\Document\Service',
                'choices' => $options['services'],
                'multiple' => true
            ])
            ->add('defaultServices', 'collection', [
                'label' => 'Услуги по умолчанию',
                'group' => 'Общая информация',
                'required' => false,
                'type' => new TariffServiceType($options['services']),
                'allow_add' => true,
                'allow_delete' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Tariff',
            'services' => []
        ]);
    }


    public function getName()
    {
        return 'mbh_price_tariff_promotions';
    }

}