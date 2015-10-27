<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PriceBundle\Document\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;


/**
 * Class TariffServiceType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class TariffServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', 'document', [
                'label' => 'form.packageServiceType.service',
                'class' => 'MBHPriceBundle:Service',
                'empty_value' => '',
                'group_by' => 'category',
                'attr' => [
                    'style' => 'width:250px',
                    'placeholder' => 'Выберите услугу',
                ],
                'choice_attr' => function(Service $service) {
                    return ['data-type' => $service->getCalcType()];
                },
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service'
            ])
            ->add('nights', 'text', [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Кол. ночей',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank()
            ])
            ->add('persons', 'text', [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Кол. персон',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
            ])
            ->add('amount', 'text', [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Кол.',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'help' => '-'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\TariffService'
        ]);
    }


    public function getName()
    {
        return 'mbh_price_tariff_service';
    }

}