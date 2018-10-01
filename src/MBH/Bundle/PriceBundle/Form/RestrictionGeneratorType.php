<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\HotelBundle\Document\RoomTypeRepository;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RestrictionGeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, array(
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.nachalo.perioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.setting',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => array('class' => 'datepicker begin-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', DateType::class, array(
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.konets.perioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.setting',
                'required' => true,
                'attr' => array('class' => 'datepicker end-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays',  InvertChoiceType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.dni.nedeli',
                'required' => false,
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.setting',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.dni.nedeli.dlya.kotorykh.budet.proizvedena.generatsiya.nalichiya.mest',
                'attr' => array('placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.vse.dni.nedeli'),
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.tipy.nomerov',
                'required' => false,
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.setting',
                'multiple' => true,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    /** @var RoomTypeRepository $dr */
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.tipy.nomerov.dlya.kotorykh.budet.proizvedena.generatsiya.tsen',
                'attr' => array('placeholder' => $options['hotel'].': mbhpricebundle.form.restrictiongeneratortype.vse.tipy.nomerov', 'class' => 'select-all'),
            ])
            ->add('tariffs', DocumentType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.tarify',
                'required' => true,
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.setting',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    /** @var TariffRepository $dr */
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'restrictions');
                },
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.tarify.dlya.kotorykh.budet.proizvedena.generatsiya.tsen',
                'attr' => array('placeholder' => $options['hotel'].': mbhpricebundle.form.restrictiongeneratortype.vse.tarify', 'class' => 'select-all'),
            ])
            ->add('minStayArrival', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min_dlina',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.dlina_broni',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('maxStayArrival', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.max_dlina',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.dlina_broni',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('minStay', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min_dlina',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.dlina_broni_skvosnoe',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('maxStay', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.max_dlina',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.dlina_broni_skvosnoe',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('minBeforeArrival', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min.dneydozayezda',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.rannee_posdnee_bronirovanie',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('maxBeforeArrival', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.max_dney_do_zaezda',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.rannee_posdnee_bronirovanie',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_menshe_odnogo_dnia'])
                ],
            ])
            ->add('closedOnArrival', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.net_zaezda',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.ogranichenie_zaezda_viezda',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny']
            ])
            ->add('closedOnDeparture', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.net_viezda',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.ogranichenie_zaezda_viezda',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
            ])
            ->add('closed', CheckboxType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.zakrito',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.ogranichenie_zaezda_viezda',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
            ])
            ->add('maxGuest', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.max_kolichestvo_gostey',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.ogranichenie_po_kolichestvu_gostey',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.kolichestvo_gostey_ne_mozhet_bit_menshe_nulia'])
                ],
            ])
            ->add('minGuest', TextType::class, [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min_kolichestvo_gostey',
                'group' => 'mbhpricebundle.form.restrictiongeneratortype.ogranichenie_po_kolichestvu_gostey',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyye.budut.udaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'mbhpricebundle.form.restrictiongeneratortype.kolichestvo_gostey_ne_mozhet_bit_menshe_nulia'])
                ],
            ])
        ;

    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if ($data['begin'] >= $data['end']) {
            $context->addViolation('mbhpricebundle.form.restrictiongeneratortype.nachalo_perioda_dolzhno_bit_menshe_konca_perioda');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370) {
            $context->addViolation('mbhpricebundle.form.restrictiongeneratortype.period_ne_mozhet_bit_bolshe_goda');
        }
    }

    public function checkGuest($data, ExecutionContextInterface $context)
    {
        if ((int)$data['maxGuest'] < (int)$data['minGuest']) {
            $context->addViolation('mbhpricebundle.form.restrictiongeneratortype.minimalnoe_kolichestvo_gostey_ne_mozhet_bit_bolshe_maximalnogo_kolichestva_gostey');
        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'constraints' => [
                new Callback([
                    'callback' => [$this, 'checkDates'],
                ]),
                new Callback([
                    'callback' => [$this, 'checkGuest'],
                ]),

            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_restriction_generator_type';
    }

}
