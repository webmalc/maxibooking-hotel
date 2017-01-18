<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvitedTouristType
 * @package MBH\Bundle\OnlineBundle\Form

 */
class InvitedTouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sex',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'label' => 'Обращение',
                'expanded' => true,
                'choices' => [
                    'Господин',
                    'Госпожа'
                ],
                'placeholder' => null,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthday', TextType::class, [
                'required' => false,
                'label' => 'Дата рождения',
                //'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthplace', TextType::class, [
                'required' => false,
                'label' => 'Место рождения',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('citizenship', TextType::class, [
                'required' => false,
                'label' => 'Гражданство',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('passport', TextType::class, [
                'required' => false,
                'label' => 'Паспорт',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('expiry', TextType::class, [
                'required' => false,
                'label' => 'Действует до',
                //'widget' => 'single_text',
                //'format' => 'yyyy-MM-dd',
                'constraints' => [
                    new NotBlank()
                ],
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\OnlineBundle\Document\InvitedTourist'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_online_bundle_invited_tourist';
    }

}
