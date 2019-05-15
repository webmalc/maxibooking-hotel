<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Form\SocialNetworking;


use MBH\Bundle\OnlineBundle\Document\SocialLink\SocialLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

class SocialNetworkingServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formModifier = function (FormEvent $event, string $eventName) {
            $builder = $event->getForm();

            /** @var SocialLink $sns */
            $sns = $event->getData();

            $builder
                ->add(
                    'key',
                    HiddenType::class
                )
                ->add(
                    'name',
                    HiddenType::class
                )
                ->add(
                    'url',
                    TextType::class,
                    [
                        'label'       => sprintf('<i class="fa fa-%s"></i> %s', $sns->getKey(), $sns->getName()),
                        'required'    => false,
                        'group'       => 'no-group',
                        'constraints' => [new Url()],
                    ]
                );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $formEvent) use ($formModifier) {
                $formModifier($formEvent, FormEvents::PRE_SET_DATA);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SocialLink::class,
        ]);
    }
}
