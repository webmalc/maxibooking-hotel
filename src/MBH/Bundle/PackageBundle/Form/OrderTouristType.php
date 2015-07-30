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
                'label' => 'form.orderTouristType.fio',
                'required' => false,
                'group' => 'form.orderTouristType.find_guest',
                'attr' => ['placeholder' => 'form.orderTouristType.placeholder_fio', 'style' => 'min-width: 500px', 'class' => 'findGuest']
            ])
            ->add('lastName', 'text', [
                'label' => 'form.orderTouristType.surname',
                'required' => true,
                'group' => 'form.orderTouristType.add_guest',
                'attr' => ['placeholder' => 'form.orderTouristType.placeholder_surname', 'class' => 'guestLastName'],
                'constraints' => [new NotBlank(), new Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'form.orderTouristType.min_name',
                    'maxMessage' => 'form.orderTouristType.max_name'
                ])]
            ])
            ->add('firstName', 'text', [
                'label' => 'form.orderTouristType.name',
                'required' => true,
                'group' => 'form.orderTouristType.add_guest',
                'attr' => ['placeholder' => 'form.orderTouristType.placeholder_name', 'class' => 'guestFirstName'],
                'constraints' => [new NotBlank(), new Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'form.orderTouristType.min_surname',
                    'maxMessage' => 'form.orderTouristType.max_surname'
                ])]
            ])
            ->add('patronymic', 'text', [
                'label' => 'form.orderTouristType.second_name',
                'required' => false,
                'group' => 'form.orderTouristType.add_guest',
                'attr' => ['placeholder' => 'form.orderTouristType.placeholder_second_name', 'class' => 'guestPatronymic'],
                'constraints' => [new Length([
                    'min' => 2,
                    'max' => 100,
                    'minMessage' => 'form.orderTouristType.min_second_name',
                    'maxMessage' => 'form.orderTouristType.max_second_name'
                ])]
            ])
            ->add('phone', 'text', array(
                'label' => 'form.orderTouristType.phone',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false,
                'attr' => array('class' => 'guestPhone', 'placeholder' => '+7 (987) 654-32-10'),
                'constraints' => []
            ))
            ->add('email', 'email', array(
                'label' => 'form.orderTouristType.email',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false,
                'attr' => array('class' => 'guestEmail'),
                'constraints' => [new Email()]
            ))
            ->add('birthday', 'date', array(
                'label' => 'form.orderTouristType.birth_date',
                'widget' => 'single_text',
                'group' => 'form.orderTouristType.add_guest',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'guestBirthday'),
                'constraints' => [new Date()]
            ))
            ->add('addToPackage', 'checkbox', array(
                'label' => 'form.orderTouristType.add_to_package',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false
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
