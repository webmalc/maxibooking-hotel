<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RestrictionGeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', 'date', array(
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.nachaloperioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => array('class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', 'date', array(
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.konetsperioda',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'required' => true,
                'attr' => array('class' => 'datepicker end-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays', 'choice', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.dninedeli',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.dninedelidlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                'attr' => array('placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.vsedninedeli'),
            ])
            ->add('roomTypes', 'document', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.tipynomerov',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.tipynomerovdlyagotorykhbudetproizvedenageneratsiyatsen',
                'attr' => array('placeholder' => $options['hotel'].': все типы номеров', 'class' => 'select-all'),
            ])
            ->add('tariffs', 'document', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.tarify',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'rooms');
                },
                'help' => 'mbhpricebundle.form.restrictiongeneratortype.tarifydlyagotorykhbudetproizvedenageneratsiyatsen',
                'attr' => array('placeholder' => $options['hotel'].': все тарифы', 'class' => 'select-all'),
            ])
            ->add('minStayArrival', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min.dlina',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStayArrival', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.maks.dlina',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minStay', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min.dlina',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStay', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.maks.dlina',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minBeforeArrival', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.min.dneydozayezda',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxBeforeArrival', 'text', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.maks.dneydozayezda',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('closedOnArrival', 'checkbox', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.netzayezda?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny']
            ])
            ->add('closedOnDeparture', 'checkbox', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.netvyyezda?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
            ])
            ->add('closed', 'checkbox', [
                'label' => 'mbhpricebundle.form.restrictiongeneratortype.zakryto?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'mbhpricebundle.form.restrictiongeneratortype.dannyyebudutudaleny'],
            ])
        ;

    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if ($data['begin'] >= $data['end']) {
            $context->addViolation('Начало периода должно быть меньше конца периода.');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370) {
            $context->addViolation('Период не может быть больше года.');
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'constraints' => new Callback([$this, 'checkDates'])
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_restriction_generator_type';
    }

}
