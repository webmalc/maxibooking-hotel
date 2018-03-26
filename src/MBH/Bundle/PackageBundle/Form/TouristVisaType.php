<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TouristVisaType extends AbstractType
{
    /** @var  FMSDictionaries */
    private $fmsDictionaries;

    public function __construct(FMSDictionaries $fmsDictionaries) {
        $this->fmsDictionaries = $fmsDictionaries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type',  InvertChoiceType::class, [
                'label' => 'tourist.visa.type_type',
                'group' => 'visa',
                'placeholder' => '',
                'choices' => [
                    'visa' => 'tourist.visa.choices.visa',
                    'residence' => 'tourist.visa.choices.residence',
                    'temporary_residence_permit' => 'tourist.visa.choices.temporary_residence_permit'
                ],
                'required' => false,
            ])
            ->add('visaCategory', InvertChoiceType::class, [
                'label' => 'tourist.visa.visa_category.label',
                'group' => 'visa',
                'choices' => $this->fmsDictionaries->getVisaCategories(),
                'required' => false,
            ])
            ->add('multiplicityType',  InvertChoiceType::class, [
                'label' => 'tourist.visa.multiplicity_type.label',
                'group' => 'visa',
                'placeholder' => '',
                'choices' => $this->fmsDictionaries->getVisaMultiplicityData(),
                'required' => false,
            ])
            ->add('visitPurpose',  InvertChoiceType::class, [
                'label' => 'tourist.visa.visit_purpose.label',
                'group' => 'visa',
                'placeholder' => '',
                'choices' => $this->fmsDictionaries->getVisitPurposesData(),
                'required' => false,
            ])
            ->add('entryGoal', InvertChoiceType::class, [
                'group' => 'visa',
                'required' => false,
                'label' => 'tourist.visa.entry_goal.label',
                'choices' => $this->fmsDictionaries->getEntryGoalOptions(),
            ])
            ->add('specialStatus', InvertChoiceType::class, [
                'label' => 'tourist.visa.special_status.label',
                'group' => 'visa',
                'choices' => $this->fmsDictionaries->getMigrationSpecStatuses(),
                'required' => false
            ])
            ->add('fmsKppId', TextType::class, [
                'label' => 'tourist.visa.fms_kpp_id.label',
                'help' => 'tourist.visa.fms_kpp_id.help',
                'group' => 'visa',
                'required' => false,
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'fmsKpp'
                ],
            ])
            ->add('series', TextType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_series',
                'required' => false,
            ])
            ->add('number', TextType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_number',
                'required' => false,
            ])
            ->add('identifier', TextType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.identifier',
                'required' => false,
            ])
            ->add('issued', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_issued',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'],
            ])
            ->add('expiry', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.type_expiry',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'],
            ])
            ->add('profession', TextType::class, [
                'label' => 'tourist.visa.type_profession',
                'group' => 'visa',
                'required' => false,
            ])
            ->add('arrivalTime', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.arrival_time',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'],
            ])
            ->add('departureTime', DateType::class, [
                'group' => 'visa',
                'label' => 'tourist.visa.departure_time',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => ['data-date-format' => 'dd.mm.yyyy', 'class' => 'input-small datepicker'],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'mbh_package_tourist_visa';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Visa'
        ]);
    }
}