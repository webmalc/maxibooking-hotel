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

                $services[$cat->getName()][$service->getId()] = $service->getName();
            }
        }

        $builder
            ->add(
                'service',
                'choice',
                [
                    'label' => 'form.packageServiceType.service',
                    'required' => true,
                    'choices' => $services,
                    'empty_value' => '',
                    'error_bubbling' => true,
                    'mapped' => false,
                    'group' => $options['form_label'],
                    'constraints' => new NotBlank(),
                    'help' => 'form.packageServiceType.reservation_add_service',
                    'data' => $options['serviceId']
                ]
            )
            ->add(
                'price',
                'text',
                [
                    'label' => 'form.packageServiceType.price',
                    'required' => true,
                    'group' => $options['form_label'],
                    'error_bubbling' => true,
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
                    'group' => $options['form_label'],
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
                    'group' => $options['form_label'],
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm']
                ]
            )
            ->add('date', 'date', array(
                    'label' => 'Дата',
                    'group' => $options['form_label'],
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => $options['package']->getBegin(),
                    'required' => true,
                    'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ))
            ->add(
                'amount',
                'text',
                [
                    'label' => 'form.packageServiceType.amount',
                    'required' => true,
                    'data' => 1,
                    'group' => $options['form_label'],
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
                'serviceId' => null,
                'form_label' => null,
                'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageService',
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_service_type';
    }

}
