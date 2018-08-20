<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ServiceType
 */
class ServiceType extends AbstractType
{
    /** @var  TranslatorInterface $translator */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'mbhpricebundle.form.servicetype.nazvaniye',
                'required' => true,
                'group' => 'price.form.public_information',
                'attr' => ['placeholder' => 'mbhpricebundle.form.servicetype.seyf']
            ])
            ->add('title', TextType::class, [
                'label' => 'mbhpricebundle.form.servicetype.vnutrenneyenazvaniye',
                'group' => 'price.form.public_information',
                'required' => false,
                'attr' => ['placeholder' => $this->translator->trans('price.form.save_summer') . ' ' . date('Y')],
                'help' => 'price.form.name_for_using_inside_maxibooking'
            ])
            ->add('international_title', TextType::class, [
                'label' => 'form.roomTypeType.international_title',
                'required' => false,
                'group' => 'price.form.public_information',
                //'help' => 'mbhpricebundle.form.servicetype.mezhdunarodnoye.nazvaniye'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'mbhpricebundle.form.servicetype.opisaniye',
                'required' => false,
                'group' => 'price.form.public_information',
                'help' => 'mbhpricebundle.form.servicetype.opisaniyeuslugidlyaonlaynbronirovaniya'
            ])
            ->add('calcType', \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhpricebundle.form.servicetype.tiprascheta',
                'group' => 'price.form.public_information',
                'required' => true,
                'placeholder' => '',
                'multiple' => false,
                'choices' => $options['calcTypes'],
            ])
            ->add('recalcWithPackage', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.servicetype.is_displaceable',
                'value' => true,
                'group' => 'price.form.public_information',
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.is_displaceable.help',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('recalcCausedByTouristsNumberChange', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.servicetype.is_recalc_with_change_touris_number.label',
                'group' => 'price.form.public_information',
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.is_recalc_with_change_touris_number.help',
                'attr' => ['class' => 'recalc-caused-by-guests'],
            ])
            ->add('includeArrival', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.servicetype.includeArrival',
                'value' => true,
                'group' => 'price.form.public_information',
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.includeArrival.help',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('includeDeparture', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.servicetype.includeDeparture',
                'value' => true,
                'group' => 'price.form.public_information',
                'required' => false,
                'help' => 'mbhpricebundle.form.servicetype.includeDeparture.help',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('price', TextType::class, [
                'label' => 'mbhpricebundle.form.servicetype.tsena',
                'group' => 'price.form.public_information',
                'required' => false,
                'attr' => ['placeholder' => 'price.form.service_not_use', 'class' => 'spinner price-spinner'],
            ])
            ->add('date', CheckboxType::class, [
                'label' => 'price.form.date',
                'group' => 'price.form.setting',
                'value' => true,
                'required' => false,
                'help' => 'price.form.use_date_when_adding_service_reservation'
            ])
            ->add('time', CheckboxType::class, [
                'label' => 'price.form.time',
                'group' => 'price.form.setting',
                'value' => true,
                'required' => false,
                'help' => 'price.form.should_i_use_time_when_i_add_service_my_reservation'
            ])
            ->add('isOnline', CheckboxType::class, [
                'label' => 'price.form.online',
                'value' => true,
                'group' => 'price.form.setting',
                'required' => false,
                'help' => 'price.form.should_i_use_service_online_booking'
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'price.form.she_on',
                'group' => 'price.form.setting',
                'value' => true,
                'required' => false,
                'help' => 'price.form.is_service_available_sale'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\Service',
            'calcTypes' => []
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_service_type';
    }
}
