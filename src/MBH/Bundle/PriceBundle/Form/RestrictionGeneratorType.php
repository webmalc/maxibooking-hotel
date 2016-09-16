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
                'label' => 'Начало периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'data' => new \DateTime('midnight'),
                'required' => true,
                'attr' => array('class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', 'date', array(
                'label' => 'Конец периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Настройки',
                'required' => true,
                'attr' => array('class' => 'datepicker end-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays', 'choice', [
                'label' => 'Дни недели',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'Дни недели для готорых будет произведена генерация наличия мест',
                'attr' => array('placeholder' => 'все дни недели'),
            ])
            ->add('roomTypes', 'document', [
                'label' => 'Типы номеров',
                'required' => false,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'Типы номеров для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'].': все типы номеров', 'class' => 'select-all'),
            ])
            ->add('tariffs', 'document', [
                'label' => 'Тарифы',
                'required' => true,
                'group' => 'Настройки',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'restrictions');
                },
                'help' => 'Тарифы для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'].': все тарифы', 'class' => 'select-all'),
            ])
            ->add('minStayArrival', 'text', [
                'label' => 'Мин. длина',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStayArrival', 'text', [
                'label' => 'Макс. длина',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minStay', 'text', [
                'label' => 'Мин. длина',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStay', 'text', [
                'label' => 'Макс. длина',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minBeforeArrival', 'text', [
                'label' => 'Мин. дней до заезда',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxBeforeArrival', 'text', [
                'label' => 'Макс. дней до заезда',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'data' => null,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('closedOnArrival', 'checkbox', [
                'label' => 'Нет заезда?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены']
            ])
            ->add('closedOnDeparture', 'checkbox', [
                'label' => 'Нет выезда?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены'],
            ])
            ->add('closed', 'checkbox', [
                'label' => 'Закрыто?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены'],
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
