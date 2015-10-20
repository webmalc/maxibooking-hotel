<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
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
                //'empty_value' => '',
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service',
            ])
            ->add('nights', 'text', [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('persons', 'text', [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('begin', 'date', [
                'label' => 'Дата',
                'group' => 'form.packageServiceType.add_service',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ])
            ->add('time', 'time', [
                'label' => 'form.packageServiceType.time',
                'required' => false,
                'group' => 'form.packageServiceType.add_service',
                'attr' => ['class' => 'sm'],
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('amount', 'text', [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm'],
                'help' => '-'
            ])
            ->add('note', 'textarea', [
                'label' => 'form.packageServiceType.comment',
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
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