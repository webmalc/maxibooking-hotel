<?php


namespace MBH\Bundle\BaseBundle\Form;


use MBH\Bundle\BaseBundle\Document\NotificationConfig;
use MBH\Bundle\BaseBundle\Lib\MessageTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationConfigType extends AbstractType
{
    const LABEL_PREFIX = 'notifier.config.label.';
    const TITLE_PREFIX = 'notifier.config.title.';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'emailClient',
                ChoiceType::class,
                [
                    'label' => 'notifier.config.label.client',
                    'required' => false,
                    'multiple' => true,
                    'choices' => array_combine(
                        MessageTypes::CLIENT_GROUP,
                        MessageTypes::CLIENT_GROUP

                    ),
                    'choice_label' => function ($value) {
                        return self::LABEL_PREFIX.$value;
                    },
                    'choice_attr' => function ($value) {
                        return ['title' => self::TITLE_PREFIX.$value];
                    }


                ]
            )
            ->add(
                'emailStuff',
                ChoiceType::class,
                [
                    'label' => 'notifier.config.label.stuff',
                    'required' => false,
                    'multiple' => true,
                    'choices' => array_combine(
                        MessageTypes::STUFF_GROUP,
                        MessageTypes::STUFF_GROUP
                    ),
                    'choice_label' => function ($value) {
                        return self::LABEL_PREFIX.$value;
                    },
                    'choice_attr' => function ($value) {
                        return ['title' => self::TITLE_PREFIX.$value];
                    }

                ]
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => NotificationConfig::class
                ]
            );
    }

}