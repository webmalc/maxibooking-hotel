<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\PriceBundle\Document\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Class TariffServiceType

 */
class TariffServiceType extends AbstractType
{
    private $services;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->services = $options['services'];

        $builder
            ->add('service', DocumentType::class, [
                'label' => 'form.packageServiceType.service',
                'class' => 'MBHPriceBundle:Service',
                'placeholder' => '',
                'attr' => [
                    'style' => 'width:250px',
                    'placeholder' => 'mbhpricebundle.form.tariffservicetype.vyberite.uslugu',
                ],
                'choice_attr' => function(Service $service) {
                    return ['data-type' => $service->getCalcType()];
                },
                'choices' => $this->services,
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service'
            ])
            ->add('nights', NumberType::class, [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'Весь срок',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
            ])
            ->add('persons', NumberType::class, [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => false,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'mbhpricebundle.form.tariffservicetype.navsekh',
                ],
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                'attr' => [
                    'style' => 'width:80px',
                    'placeholder' => 'mbhpricebundle.form.tariffservicetype.kol-vo',
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
            'services' => []
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_price_tariff_service';
    }

}