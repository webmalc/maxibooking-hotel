<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageServiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $services = [];
        if (!$options['package'] instanceof Package) {
            throw new \Exception('Package required.');
        }
        foreach ($options['package']->getTariff()->getHotel()->getServicesCategories() as $cat) {
            foreach ($cat->getServices() as $service) {

                if (!$service->getIsEnabled()) {
                        continue;
                }

                $services[$cat->getName()][$service->getId()] = $service;
            }
        }

        $builder
            ->add(
                'service',
                'document',
                [
                    'label' => 'form.packageServiceType.service',
                    'class' => 'MBHPriceBundle:Service',
                    'choices' => $services,
                    'empty_value' => '',
                    'group' => 'form.packageServiceType.add_service',
                    'help' => 'form.packageServiceType.reservation_add_service',
                ]
            )
            ->add(
                'price',
                'text',
                [
                    'label' => 'form.packageServiceType.price',
                    'required' => true,
                    'group' => 'form.packageServiceType.add_service',
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'price-spinner sm']
                ]
            )
            ->add(
                'nights',
                'text',
                [
                    'label' => 'form.packageServiceType.nights_amount',
                    'required' => true,
                    'data' => 1,
                    'group' => 'form.packageServiceType.add_service',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm']
                ]
            )
            ->add(
                'persons',
                'text',
                [
                    'label' => 'form.packageServiceType.guests_amount',
                    'required' => true,
                    'data' => 1,
                    'group' => 'form.packageServiceType.add_service',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm']
                ]
            )
            ->add('begin', 'date', array(
                    'label' => 'Дата',
                    'group' => 'form.packageServiceType.add_service',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ))
            ->add(
                'time',
                'datetime',
                [
                    'label' => 'form.packageServiceType.time',
                    'required' => false,
                    'group' => 'form.packageServiceType.add_service',
                    'attr' => ['class' => 'sm'],
                    'time_widget' => 'single_text',
                    'date_widget' => 'single_text',
                    'html5' => false,
                ]
            )
            ->add(
                'amount',
                'text',
                [
                    'label' => 'form.packageServiceType.amount',
                    'required' => true,
                    'data' => 1,
                    'group' => 'form.packageServiceType.add_service',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm'],
                    'help' => '-'
                ]
            )
            ->add('note', 'textarea', [
                    'label' => 'form.packageServiceType.comment',
                    'required' => false,
            ])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'package' => null,
                'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageService',
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_service_type';
    }

}
