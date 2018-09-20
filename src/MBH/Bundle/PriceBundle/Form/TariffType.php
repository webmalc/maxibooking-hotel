<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TariffType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $formTariff = $builder->getData();
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'mbhpricebundle.form.tarifftype.nazvaniye',
                'group' => 'price.form.public_information',
                'required' => true,
                'attr' => ['placeholder' => 'mbhpricebundle.form.tarifftype.osnovnoy']
            ])
            ->add('title', TextType::class, [
                'label' => 'mbhpricebundle.form.tarifftype.vnutrenneye.nazvaniye',
                'group' => 'price.form.public_information',
                'required' => false,
                'attr' => ['placeholder' => $this->translator->trans('price.form.public_summer') . ' ' . date('Y')],
                'help' => 'price.form.nazvanie_dla_ispolzovania_vnutri'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'mbhpricebundle.form.tarifftype.opisaniye',
                'group' => 'price.form.public_information',
                'required' => false,
                'help' => 'mbhpricebundle.form.tarifftype.opisaniye.tarifa.dlya.onlayn.bronirovaniya'
            ])
            ->add(
                'begin',
                DateType::class,
                array(
                    'label' => 'mbhpricebundle.form.tarifftype.nachalo',
                    'group' => 'form.tariffType.conditions_and_restrictions',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'price.form.s_kakogo_chisla_ispolzuetsa_tarif',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'mbhpricebundle.form.tarifftype.neogranichen'
                    ),
                )
            )
            ->add(
                'end',
                DateType::class,
                array(
                    'label' => 'mbhpricebundle.form.tarifftype.konets',
                    'group' => 'form.tariffType.conditions_and_restrictions',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'price.form.po_kakoe_chislo_ispolzuetsa_tarif',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                        'placeholder' => 'mbhpricebundle.form.tarifftype.neogranichen'
                    ),
                )
            );
        $conditions = PromotionConditionFactory::getAvailableConditions();
        $builder
            ->add('condition',  InvertChoiceType::class, [
                'label' => 'form.promotionType.label.condition',
                'required' => false,
                'group' => 'form.tariffType.conditions_and_restrictions',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('condition_quantity', NumberType::class, [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'form.tariffType.conditions_and_restrictions',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('additional_condition',  InvertChoiceType::class, [
                'label' => 'form.promotionType.label.add_condition',
                'required' => false,
                'group' => 'form.tariffType.conditions_and_restrictions',
                'choices' => array_combine($conditions, $conditions),
                'choice_label' => function ($value) {
                    return 'form.promotionType.choice_label.condition.' . $value;
                }
            ])
            ->add('additional_condition_quantity', NumberType::class, [
                'label' => 'form.promotionType.label.condition_quantity',
                'group' => 'form.tariffType.conditions_and_restrictions',
                'required' => false,
                'error_bubbling' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
            ])
            ->add('minPerPrepay', TextType::class, [
                'label' => 'form.tariffType.minPrepay',
                'group' => 'form.tariffType.conditions_and_restrictions',
                'required' => false,
                'attr' => ['placeholder' => 'form.tariffType.minPerPrepay', 'class' => 'spinner'],
                'help' => 'form.tariffType.help'
            ]);
        $builder
            ->add('isOnline', CheckboxType::class, [
                'label' => 'price.form.online',
                'group' => 'configuration',
                'value' => true,
                'required' => false,
                'help' => 'price.form.using_tariff_in_online_booking'
            ])
            ->add(
                'childAge',  InvertChoiceType::class,
                [
                    'label' => 'mbhpricebundle.form.tarifftype.rebenok.do',
                    'group' => 'configuration',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'price.form.what_age_is_client_considered_child'
                ]
            )
            ->add(
                'infantAge',  InvertChoiceType::class,
                [
                    'label' => 'mbhpricebundle.form.tarifftype.infant.do',
                    'group' => 'configuration',
                    'required' => false,
                    'multiple' => false,
                    'choices' => range(0, 18),
                    'attr' => array('class' => 'input-xxs plain-html'),
                    'help' => 'price.form.what_age_is_client_considered_infant'
                ]
            )
            ->add('mergingTariff', DocumentType::class, [
                'label' => 'price.form.use_combination',
                'group' => 'configuration',
                'class' => Tariff::class,
                'query_builder' => function(TariffRepository $repository) use ($options, $formTariff) {
                    $qb = $repository->createQueryBuilder();
                    $qb
                        ->field('hotel')->equals($options['hotel'])
                        ->field('isEnabled')->equals(true);
                    if (!is_null($formTariff)) {
                        $qb->field('id')->notEqual($formTariff->getId());
                    }

                    return $qb;
                },
                'required' => false,
                'help' => 'mbhpricebundle.form.tarifftype.ispolzovatdlyakombinirovaniya.help'
            ])
            ->add('position', NumberType::class, [
                'label' => 'position',
                'help' => 'position.help',
                'group' => 'configuration',
                'required' => true,
                'attr' => [
                    'class' => 'spinner-0',
                ],
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'price.form.on',
                'group' => 'configuration',
                'value' => true,
                'required' => false,
                'help' => 'price.form.tariff_used_search'
            ])
            ->add(
                'isOpen',
                CheckboxType::class,
                [
                    'label'    => 'price.form.is_open',
                    'group'    => 'configuration',
                    'required' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Tariff::class,
            'hotel'      => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_tariff_main_type';
    }

}
