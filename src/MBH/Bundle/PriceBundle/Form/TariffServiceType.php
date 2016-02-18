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

 */
class TariffServiceType extends AbstractType
{
    private $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('service', 'document', [
                'label' => 'form.packageServiceType.service',
                'class' => 'MBHPriceBundle:Service',
                'empty_value' => '',
                'attr' => [
                    'style' => 'width:250px',
                    'placeholder' => 'Выберите услугу',
                ],
                'choice_attr' => function(Service $service) {
                    return ['data-type' => $service->getCalcType()];
                },
                'choices' => $this->services,
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service'
            ])
            ->add('nights', 'number', [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Весь срок',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
            ])
            ->add('persons', 'number', [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'На всех',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
            ])
            ->add('amount', 'number', [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Кол-во',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'help' => '-'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PriceBundle\Document\TariffService',
        ]);
    }


    public function getName()
    {
        return 'mbh_price_tariff_service';
    }

}