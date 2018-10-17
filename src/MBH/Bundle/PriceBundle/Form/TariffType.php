<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Query\Builder;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use MBH\Bundle\PriceBundle\Lib\TariffCombinationHolder;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class TariffType extends AbstractType
{
    private $translator;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Tariff $formTariff */
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
            );

        $data = $this->getUseTariffs($formTariff, $options);
        $builder->setAttribute('max_amount_tariffs', count($data));

        if ($formTariff !== null) {

            $builder
                ->add(
                    'tariffCombinationHolders',
                    TariffCombinationFilterType::class,
                    [
                        'group'          => 'configuration',
                        'label'          => 'price.form.use_combination',
                        'entry_type'     => TariffCombinationType::class,
                        'entry_options'  => [
                            'label'              => false,
                            'tariffs_for_select' => $data,
                            'group'              => 'no-group',
                            'parent_tariff'      => $formTariff,
                        ],
                        'prototype_name' => 'combo_tariff',
                        'allow_add'      => true,
                        'allow_delete'   => true,
                        'prototype'      => true,
                        'required'       => false,
                        'attr'           => [
                            'class' => 'my-selector',
                        ],
//                        'help'           => 'mbhpricebundle.form.tarifftype.ispolzovatdlyakombinirovaniya.help',
                    ]
                );
        }

        $builder
            ->add('position', NumberType::class, [
                'label'    => 'position',
                'help'     => 'position.help',
                'group'    => 'configuration',
                'required' => true,
                'attr'     => [
                    'class' => 'spinner-0',
                ],
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label'    => 'price.form.on',
                'group'    => 'configuration',
                'value'    => true,
                'required' => false,
                'help'     => 'price.form.tariff_used_search',
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['max_amount_tariffs'] = $form->getConfig()->getAttribute('max_amount_tariffs');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('max_amount_tariffs');

        $resolver->setDefaults([
            'data_class' => Tariff::class,
            'hotel'      => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_tariff_main_type';
    }

    /**
     * @param Tariff|null $tariff
     * @param array $options
     * @return array
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function getUseTariffs(?Tariff $tariff, array $options): array
    {
        $hotel = $options['hotel'];

        /** @var Builder $qb */
        $qb = $this->container->get('doctrine.odm.mongodb.document_manager')
            ->getRepository('MBHPriceBundle:Tariff')
            ->createQueryBuilder()
            ->field('hotel')->equals($hotel)
            ->field('isEnabled')->equals(true);

        if ($tariff !== null) {
            $qb->field('id')->notEqual($tariff->getId());
        }

        $qb->select('fullTitle');
        $qb->hydrate(false);

        $data = [];

        foreach ($qb->getQuery()->execute() as $id => $value) {
            $data[$id] = $value['fullTitle'];
        }

        return $data;
    }

}
