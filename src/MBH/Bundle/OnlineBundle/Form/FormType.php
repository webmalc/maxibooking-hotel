<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\OnlineBundle\Document\FormConfig;

class FormType extends AbstractType
{
    private $countryType;

    public function __construct($countryType)
    {
        $this->countryType = $countryType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'hotels',
                DocumentType::class,
                [
                    'label' => 'form.formType.hotels',
                    'class' => 'MBH\Bundle\HotelBundle\Document\Hotel',
                    'group' => 'form.formType.parameters',
                    'required' => false,
                    'multiple' => true,
                    'attr' => ['placeholder' => 'form.formType.hotels_placeholder'],
                    'help' => 'form.formType.hotels_desc'
                ]
            )
            ->add(
                'enabled',
                CheckboxType::class,
                [
                    'label' => 'form.formType.is_turned_on',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.use_online_form'
                ]
            )
            ->add('resultsUrl', TextType::class, [
                'label' => 'form.formType.resultsUrl_label',
                'group' => 'form.formType.parameters',
                'required' => true,
                'help' => 'form.formType.resultsUrl_help'
            ])
            ->add(
                'nights',
                CheckboxType::class,
                [
                    'label' => 'form.formType.should_we_use_nights_field',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_check_in_date_or_check_in_and_check_out_date'
                ]
            )
            ->add(
                'roomTypes',
                CheckboxType::class,
                [
                    'label' => 'form.formType.room_types',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_room_type_field_in_online_form'
                ]
            )
            ->add(
                'roomTypeChoices',
                DocumentType::class,
                [
                    'label' => 'form.formType.room_type_choices',
                    'class' => 'MBH\Bundle\HotelBundle\Document\RoomType',
                    'group' => 'form.formType.parameters',
                    'required' => false,
                    'multiple' => true,
                    'group_by' => 'hotel',
                    'attr' => ['placeholder' => 'form.formType.room_type_choices_placeholder'],
                    'help' => 'form.formType.room_type_choices_desc'
                ]
            )
            ->add(
                'tourists',
                CheckboxType::class,
                [
                    'label' => 'form.formType.are_there_guests',
                    'group' => 'form.formType.parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.formType.should_we_use_guests_amount_field_in_online_form'
                ]
            )
            ->add('isDisplayChildrenAges', CheckboxType::class, [
                'label' => 'form.formType.used_children_ages.label',
                'group' => 'form.formType.parameters',
                'value' => true,
                'required' => false,
                'help' => 'form.formType.used_children_ages.help'
            ])
            ->add('maxPackages', ChoiceType::class, [
                'label' => 'form.formType.max_packages.label',
                'group' => 'form.formType.parameters',
                'choices' => array_combine(range(1, 20), range(1, 20)),
                'required' => true,
                'help' => 'form.formType.max_packages.help'
            ])
            ->add('personalDataPolicies', TextType::class, [
                'label' => 'form.formType.pers_data_policies_url.label',
                'help' => 'form.formType.pers_data_policies_url.help',
                'required' => false,
                'group' => 'form.formType.parameters',
            ]);
        if ($this->countryType === 'ru' || $this->countryType == 'kaz') {
            $innLabel = $this->countryType == 'ru' ? 'form.formType.is_request_inn.label' : 'form.formType.is_request_inn.kaz.label';
            $innHelp = $this->countryType == 'ru' ? 'form.formType.is_request_inn.help' : 'form.formType.is_request_inn.kaz.help';
            $builder
                ->add('requestInn', CheckboxType::class, [
                    'label' => $innLabel,
                    'help' => $innHelp,
                    'required' => false,
                    'group' => 'form.formType.parameters',
                ]);
        }
        $builder
            ->add('requestTouristDocumentNumber', CheckboxType::class, [
                'label' => 'form.formType.is_request_tourist_document_number.label',
                'help' => 'form.formType.is_request_tourist_document_number.help',
                'required' => false,
                'group' => 'form.formType.parameters',
            ])
            ->add('requestPatronymic', CheckboxType::class, [
                'label' => 'form.formType.is_request_tourist_patronymic.label',
                'help' => 'form.formType.is_request_tourist_patronymic.help',
                'required' => false,
                'group' => 'form.formType.parameters',
            ])
            ->add('isFullWidth', CheckboxType::class, [
                'group' => 'form.formType.css',
                'label' => 'form.formType.frame_width.is_full_width.label',
                'required' => false,
                'help' => 'form.formType.frame_width.is_full_width.help'
            ])
            ->add('frameWidth', IntegerType::class, [
                'label' => 'form.formType.frame_width.label',
                'group' => 'form.formType.css',
                'required' => true,
                'help' => 'form.formType.frame_width.help'
            ])
            ->add('frameHeight', IntegerType::class, [
                'label' => 'form.formType.frame_height.label',
                'group' => 'form.formType.css',
                'required' => true,
                'help' => 'form.formType.frame_height.help'
            ])
            ->add(
                'paymentTypes',
                InvertChoiceType::class,
                [
                    'group' => 'form.formType.payment',
                    'choices' => $options['paymentTypes'],
                    'label' => 'form.formType.payment_type',
                    'multiple' => true,
                    'help' => 'form.formType.reservation_payment_types_with_online_form'
                ]
            )
            ->add(
                'css',
                TextareaType::class,
                [
                    'group' => 'form.formType.css',
                    'label' => 'form.formType.css_label',
                    'required' => false,
                    'help' => 'form.formType.css_help',
                    'attr' => ['rows' => 60]
                ]
            )
            ->add(
                'isHorizontal',
                CheckboxType::class,
                [
                    'group' => 'form.formType.css',
                    'label' => 'form.formType.is_horizontal.label',
                    'required' => false,
                    'help' => 'form.formType.is_horizontal.help',
                ]
            )
            ->add('js',
                TextareaType::class,
                [
                    'group' => 'form.formType.js_group',
                    'label' => 'form.formType.js_label',
                    'required' => false,
                    'attr' => ['rows' => 10]
                ]);
        if ($options['user'] === User::SYSTEM_USER) {
            $builder->add(
                'formTemplate',
                TextareaType::class,
                [
                    'group' => 'form.formType.template',
                    'label' => 'form.formType.template_label',
                    'required' => false,
                    'help' => 'form.formType.template_help',
                    'attr' => ['rows' => 60],
                ]
            );
        }

        $builder->add(
            'cssLibraries',
            ChoiceType::class,
            [
                'group' => 'form.formType.css',
                'choices' => FormConfig::getCssLibrariesList(),
                'required' => false,
                'label' => 'form.formType.label.css_libraries',
                'help' => 'form.formType.help.css_libraries',
                'choice_attr' => function ($value) {
                    return [
                        'title' => $value,
                    ];
                },
                'multiple' => true
            ]
        )
            ->add(
                'theme',
                ChoiceType::class,
                [
                    'group' => 'form.formType.css',
                    'choices' => FormConfig::getThemes(),
                    'required' => false,
                    'label' => 'form.formType.theme_label',
                    'help' => 'https://bootswatch.com'
                ]
            );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        //TODO: implement restore twig template default?
        if (isset($view->children['formTemplate'])) {
            $view->children['formTemplate']->vars['twig_sample'] = null;
        }
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\OnlineBundle\Document\FormConfig',
                'paymentTypes' => [],
                'user' => null
            )
        );
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_onlinebundle_form_type';
    }
}
