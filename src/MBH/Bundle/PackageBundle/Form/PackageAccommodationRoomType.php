<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageAccommodationRoomType

 */
class PackageAccommodationRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'label' => 'form.package_accommodation_type.check_in',
                'widget' => 'single_text',
                'help' => 'form.package_accommodation_type.check_in.help',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => array(
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('end', DateType::class, [
                'label' => 'form.package_accommodation_type.check_out',
                'widget' => 'single_text',
                'help' => 'form.package_accommodation_type.check_out.help',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => array(
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('isAutomaticallyChangeable', CheckboxType::class, [
                'label' => 'form.package_accommodation_type.is_automatically_changeable.label',
                'help' => 'form.package_accommodation_type.is_automatically_changeable.help',
                'required' => false
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.package_accommodation_type.note',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageAccommodation',
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_accommodation_room_type';
    }

}
