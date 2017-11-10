<?php

namespace MBH\Bundle\UserBundle\Form;

use MBH\Bundle\BillingBundle\Lib\Model\ClientService;
use MBH\Bundle\BillingBundle\Lib\Model\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $services = [];
        /** @var Service $service */
        foreach ($options['services'] as $service) {
            $services[$service->getTitle()] = $service->getId();
        }

        $builder
            ->add('service', ChoiceType::class, [
                'choices' => $services,
                'label' => 'form.client_service_type.service.label'
            ])
            ->add('period', ChoiceType::class, [
                'label' => 'form.client_service_type.period.label',
                //TODO: Заменить
                'choices' => [
                    '1 месяц' => 1,
                    '3 месяца' => 3,
                    '6 месяцев' => 6
                ]
            ])
            ->add('price', TextType::class, [
                'label' => 'form.client_service_type.price.label',
                'attr' => [
                    'readonly' => true
                ],
                'required' => false
            ])
            ->add('quantity', TextType::class, [
                'label' => 'form.client_service_type.quantity.label'
            ])
            ->add('units', TextType::class, [
                'label' => 'form.client_service_type.units.label',
                'attr' => [
                    'readonly' => true
                ],
                'required' => false
            ])
            ->add('cost', TextType::class, [
                'label' => 'form.client_service_type.cost.label',
                'attr' => [
                    'readonly' => true
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'clientServices' => null,
            'services' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbhuser_bundle_client_service_type';
    }
}
