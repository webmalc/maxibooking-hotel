<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class InvitedTouristType
 * @package MBH\Bundle\OnlineBundle\Form
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class InvitedTouristType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', 'text', [
                'required' => false,
                'label' => 'Имя',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('lastName', 'text', [
                'required' => false,
                'label' => 'Фамилия',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sex', 'choice', [
                'required' => false,
                'label' => 'Обращение',
                'expanded' => true,
                'choices' => [
                    'Господин',
                    'Госпожа'
                ],
                'empty_value' => null,
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthday', 'text', [
                'required' => false,
                'label' => 'Дата рождения',
                //'widget' => 'single_text',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('birthplace', 'text', [
                'required' => false,
                'label' => 'Место рождения',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('citizenship', 'text', [
                'required' => false,
                'label' => 'Гражданство',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('passport', 'text', [
                'required' => false,
                'label' => 'Паспорт',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('expiry', 'text', [
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
            //'compound' => true
        ]);
    }

    public function getName()
    {
        return 'mbh_online_bundle_invited_tourist';
    }

}
