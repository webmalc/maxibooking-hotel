<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('begin', DateType::class, array(
                'label' => 'Начало периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Общая информация',
                'required' => true,
                'attr' => array('class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('end', DateType::class, array(
                'label' => 'Конец периода',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'group' => 'Общая информация',
                'required' => true,
                'attr' => array('class' => 'datepicker end-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                'constraints' => [new NotBlank(), new Date()],
            ))
            ->add('weekdays', ChoiceType::class, [
                'label' => 'Дни недели',
                'required' => false,
                'group' => 'Общая информация',
                'multiple' => true,
                'choices' => $options['weekdays'],
                'help' => 'Дни недели для готорых будет произведена генерация наличия мест',
                'attr' => array('placeholder' => 'все дни недели'),
            ])
            ->add('roomTypes', DocumentType::class, [
                'label' => 'Типы номеров',
                'required' => false,
                'group' => 'Общая информация',
                'multiple' => true,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchQueryBuilder($options['hotel']);
                },
                'help' => 'Типы номеров для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'].': все типы номеров', 'class' => 'select-all'),
            ])
            ->add('tariffs', DocumentType::class, [
                'label' => 'Тарифы',
                'required' => true,
                'group' => 'Общая информация',
                'multiple' => true,
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->fetchChildTariffsQuery($options['hotel'], 'restrictions');
                },
                'help' => 'Тарифы для готорых будет произведена генерация цен',
                'attr' => array('placeholder' => $options['hotel'].': все тарифы', 'class' => 'select-all'),
            ])
            ->add('minStayArrival', TextType::class, [
                'label' => 'Мин. длина',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStayArrival', TextType::class, [
                'label' => 'Макс. длина',
                'group' => 'Длина брони (заезд)',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minStay', TextType::class, [
                'label' => 'Мин. длина',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxStay', TextType::class, [
                'label' => 'Макс. длина',
                'group' => 'Длина брони (сквозное)',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('minBeforeArrival', TextType::class, [
                'label' => 'Мин. дней до заезда',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('maxBeforeArrival', TextType::class, [
                'label' => 'Макс. дней до заезда',
                'group' => 'Раннее/позднее бронирование',
                'required' => false,
                'attr' => ['class' => 'spinner-1', 'placeholder' => 'данные будут удалены'],
                'constraints' => [
                    new Range(['min' => 1, 'minMessage' => 'Период не может быть меньше одного дня'])
                ],
            ])
            ->add('closedOnArrival', CheckboxType::class, [
                'label' => 'Нет заезда?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены']
            ])
            ->add('closedOnDeparture', CheckboxType::class, [
                'label' => 'Нет выезда?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены'],
            ])
            ->add('closed', CheckboxType::class, [
                'label' => 'Закрыто?',
                'group' => 'Ограничение заезда/выезда',
                'value' => true,
                'required' => false,
                'attr' => ['placeholder' => 'данные будут удалены'],
            ])
            ->add('addRestrictions',  CheckboxType::class, [
                'label' => 'Дополнить?',
                'help' => 'Дополнить вместо перезаписи?',
                'group' => 'Настройки',
                'required' => false
            ]);
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
