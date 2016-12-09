<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Form\LanguageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderTouristType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tourist', TextType::class, [
                'label' => 'form.orderTouristType.fio',
                'required' => false,
                'group' => 'form.orderTouristType.find_guest',
                'attr' => ['placeholder' => 'form.orderTouristType.placeholder_fio', 'style' => 'min-width: 500px', 'class' => 'findGuest']
            ])
            ->add('lastName', TextType::class, [
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
            ->add('firstName', TextType::class, [
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
            ->add('patronymic', TextType::class, [
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
            ->add('phone', TextType::class, array(
                'label' => 'form.orderTouristType.phone',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false,
                'attr' => array('class' => 'guestPhone', 'placeholder' => '+7 (987) 654-32-10'),
                'constraints' => []
            ))
            ->add('email', EmailType::class, array(
                'label' => 'form.orderTouristType.email',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false,
                'attr' => array('class' => 'guestEmail'),
                'constraints' => [new Email()]
            ))
            ->add('birthday', DateType::class, array(
                'label' => 'form.orderTouristType.birth_date',
                'widget' => 'single_text',
                'group' => 'form.orderTouristType.add_guest',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => array('data-date-format' => 'dd.mm.yyyy', 'class' => 'guestBirthday'),
                'constraints' => [new Date()]
            ))
            ->add('communicationLanguage', LanguageType::class, [
                'label' => 'form.touristType.communication_language',
                'group' => 'form.orderTouristType.add_guest',
                'attr' => ['class' => 'guestCommunicationLanguage'],
                'required' => false,
            ])
        ;

        if ($options['guest']) {
            $builder->add('addToPackage', CheckboxType::class, [
                'label' => 'form.orderTouristType.add_to_package',
                'group' => 'form.orderTouristType.add_guest',
                'required' => false
            ]);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'guest' => true
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_order_tourist_type';
    }

}
