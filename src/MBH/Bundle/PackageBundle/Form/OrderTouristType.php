<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;

class OrderTouristType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('tourist', 'text', [
                    'label' => 'ФИО',
                    'required' => false,
                    'group' => 'Найти гостя',
                    'attr' => ['placeholder' => 'Иванов Иван Иванович', 'style' => 'min-width: 500px', 'class' => 'findGuest']
                ])
                ->add('lastName', 'text', [
                    'label' => 'Фамилия',
                    'required' => true,
                    'group' => 'Добавить гостя',
                    'attr' => ['placeholder' => 'Иванов', 'class' => 'guestLastName'],
                    'constraints' => [new NotBlank(), new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткое имя',
                        'maxMessage' => 'Слишком длинное имя'
                    ])]
                ])
                ->add('firstName', 'text', [
                    'label' => 'Имя',
                    'required' => true,
                    'group' => 'Добавить гостя',
                    'attr' => ['placeholder' => 'Иван', 'class' => 'guestFirstName'],
                    'constraints' => [new NotBlank(), new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткая фамилия',
                        'maxMessage' => 'Слишком длинная фамилия'
                    ])]
                ])
                ->add('patronymic', 'text', [
                    'label' => 'Отчество',
                    'required' => false,
                    'group' => 'Добавить гостя',
                    'attr' => ['placeholder' => 'Иванович', 'class' => 'guestPatronymic'],
                    'constraints' => [new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Слишком короткое отчество',
                        'maxMessage' => 'Слишком длинное отчество'
                    ])]
                ])
                ->add('birthday', 'date', array(
                    'label' => 'Дата рождения',
                    'widget' => 'single_text',
                    'group' => 'Добавить гостя',
                    'format' => 'dd.MM.yyyy',
                    'required' => false,
                    'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'guestBirthday'),
                    'constraints' => [new Date()]
                ))
                ->add('phone', 'text', array(
                    'label' => 'Телефон',
                    'group' => 'Добавить гостя',
                    'required' => false,
                    'attr' => array('class' => 'guestPhone', 'placeholder' => '+7 (987) 654-32-10'),
                    'constraints' => []
                ))
                ->add('email', 'email', array(
                    'label' => 'E-mail',
                    'group' => 'Добавить гостя',
                    'required' => false,
                    'attr' => array('class' => 'guestEmail'),
                    'constraints' => [new Email()]
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_order_tourist_type';
    }

}
