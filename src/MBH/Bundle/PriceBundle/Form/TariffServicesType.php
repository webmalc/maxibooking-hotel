<?php

namespace MBH\Bundle\PriceBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TariffServicesType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
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
                'group_by' => 'category',
                'class' => 'MBH\Bundle\PriceBundle\Document\Service',
                /*'query_builder' => function(DocumentRepository $servicesRepository) {
                    return $servicesRepository->createQueryBuilder()->field('enabled')->equals(true);
                },*/
                'multiple' => true
            ])
            ->add('defaultServices', 'collection', [
                'label' => 'Услуги по умолчанию',
                'group' => 'Общая информация',
                'required' => false,
                'attr' => ['data-placeholder' => 'Все услуги'],
                'mapped' => false,
                'type' => new TariffServiceType(),
                //'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
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