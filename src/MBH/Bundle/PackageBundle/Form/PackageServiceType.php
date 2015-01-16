<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageServiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $services = [];
        if ($options['package']) {
            foreach ($options['package']->getTariff()->getHotel()->getServicesCategories() as $cat) {
                foreach ($cat->getServices() as $service) {

                    if (empty($service->getPrice()) || !$service->getIsEnabled()) {
                        continue;
                    }

                    $services[$cat->getName()][$service->getId()] = $service->getName();
                }
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
                    'constraints' => new NotBlank()
                ]
            )
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
                    'attr' => ['class' => 'spinner']
                ]
            );
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
