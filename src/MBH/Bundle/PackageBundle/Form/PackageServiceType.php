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
                    'label' => 'Услуга',
                    'required' => true,
                    'choices' => $services,
                    'empty_value' => '',
                    'error_bubbling' => true,
                    'group' => 'Добавить услугу',
                    'constraints' => new NotBlank(),
                    'help' => 'Услуга для добавления к броне'
                ]
            )
            ->add(
                'price',
                'text',
                [
                    'label' => 'Цена',
                    'required' => true,
                    'group' => 'Добавить услугу',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'price-spinner sm']
                ]
            )
            ->add(
                'nights',
                'text',
                [
                    'label' => 'Количество суток',
                    'required' => true,
                    'data' => 1,
                    'group' => 'Добавить услугу',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm']
                ]
            )
            ->add(
                'persons',
                'text',
                [
                    'label' => 'Количество гостей',
                    'required' => true,
                    'data' => 1,
                    'group' => 'Добавить услугу',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm']
                ]
            )
            ->add('date', 'date', array(
                    'label' => 'Дата',
                    'group' => 'Добавить услугу',
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
                    'label' => 'Количество',
                    'required' => true,
                    'data' => 1,
                    'group' => 'Добавить услугу',
                    'error_bubbling' => true,
                    'constraints' => new NotBlank(),
                    'attr' => ['class' => 'spinner sm'],
                    'help' => '-'
                ]
            )
            ->add('note', 'textarea', [
                    'label' => 'Комментарий',
                    'required' => false,
            ])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'package' => null
            ]
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_service_type';
    }

}
