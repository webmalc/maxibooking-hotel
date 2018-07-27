<?php

namespace MBH\Bundle\HotelBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BaseBundle\Form\FacilitiesType;
use MBH\Bundle\CashBundle\Document\CardType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HotelExtendedType extends AbstractType
{
    /** @var  DocumentManager */
    private $dm;
    private $smokingPolicyOptions;

    public function __construct(DocumentManager $dm, $smokingPolicyOptions)
    {
        $this->dm = $dm;
        $this->smokingPolicyOptions = $smokingPolicyOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating', TextType::class, [
                'label' => 'form.hotelExtendedType.how_many_stars_hotel',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
//            ->add('type', InvertChoiceType::class, [
//                'label' => 'form.hotelExtendedType.hotel_type',
//                'group' => 'form.hotelExtendedType.parameters',
//                'required' => false,
//                'choices' => (isset($options['config']['types'])) ? $options['config']['types'] : [],
//                'multiple' => true
//            ])
//            ->add('theme', InvertChoiceType::class, [
//                'label' => 'form.hotelExtendedType.hotel_theme',
//                'group' => 'form.hotelExtendedType.parameters',
//                'required' => false,
//                'choices' => (isset($options['config']['themes'])) ? $options['config']['themes'] : [],
//                'multiple' => true
//            ])
            ->add('facilities', FacilitiesType::class, [
                'label' => 'form.hotelExtendedType.hotel_amenities',
                'group' => 'form.hotelExtendedType.parameters',
                'required' => false,
            ])
            ->add('acceptedCardTypes', DocumentType::class, [
                'group' => 'form.hotelExtendedType.accepted_payment_types',
                'label' => 'form.hotelExtendedType.accepted_card_type.label',
                'help' => 'form.hotelExtendedType.accepted_card_type.help',
                'class' => CardType::class,
                'placeholder' => '',
                'required' => false,
                'multiple' => true
            ])
//            ->add('isInvoiceAccepted', CheckboxType::class, [
//                    'group' => 'form.hotelExtendedType.accepted_payment_types',
//                    'label' => 'form.hotelExtendedType.is_invoice_accepted.label',
//                    'value' => true,
//                    'required' => false,
//                    'help' => 'form.hotelExtendedType.is_invoice_accepted.help'
//                ])
            //TODO: Необходимы данные на разных языках
            ->add('checkinoutPolicy', TextareaType::class, [
                'label' => 'form.hotelExtendedType.check_in_out_policy.label',
                'help' => 'form.hotelExtendedType.check_in_out_policy.help',
                'required' => false,
                'group' => 'form.hotelExtendedType.parameters',
                'attr' => [
                    'placeholder' => 'form.hotelExtendedType.check_in_out_policy.placeholder'
                ]
            ])
            ->add('smokingPolicy', InvertChoiceType::class, [
                'label' => 'form.hotelExtendedType.smoking_policy.label',
                'required' => false,
                'group' => 'form.hotelExtendedType.parameters',
                'choices' => $this->smokingPolicyOptions
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
            'config' => null,
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_bundle_hotelbundle_hotel_extended_type';
    }

}
