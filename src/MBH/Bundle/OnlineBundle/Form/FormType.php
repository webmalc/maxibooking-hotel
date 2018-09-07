<?php

namespace MBH\Bundle\OnlineBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BillingBundle\Lib\Model\Country;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormType extends AbstractType implements DecorationTypeInterface
{
    use DecorationTypeTrait;

    const PREFIX = 'mbh_bundle_onlinebundle_form_type';

    private $clientManager;
    private $paymentTypes;

    public function __construct(ClientManager $clientManager, $onlineFormParams)
    {
        $this->clientManager = $clientManager;
        $this->paymentTypes = $onlineFormParams['payment_types'];
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $clientCountry = $this->clientManager->getClient()->getCountry();

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
        if ($clientCountry === Country::RUSSIA_TLD || $clientCountry == Country::KAZAKHSTAN_TLD) {
            $innLabel = $clientCountry == Country::RUSSIA_TLD ? 'form.formType.is_request_inn.label' : 'form.formType.is_request_inn.kaz.label';
            $innHelp = $clientCountry == Country::RUSSIA_TLD ? 'form.formType.is_request_inn.help' : 'form.formType.is_request_inn.kaz.help';
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
            ]);

        $builder
            ->add($this->isFullWidth($builder))
            ->add($this->frameWidth($builder))
            ->add($this->frameHeight($builder))
            ->add($this->css($builder))
            ->add($this->isHorizontal($builder))
            ->add($this->cssLibraries($builder))
            ->add($this->theme($builder));

        $builder->add(
                'paymentTypes',
                PaymentTypesType::class,
                [
                    'group' => 'form.formType.payment',
                    'label' => 'form.formType.payment_type',
                    'help' => 'form.formType.reservation_payment_types_with_online_form'
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
                'user' => null
            )
        );
    }

    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}
