<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
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