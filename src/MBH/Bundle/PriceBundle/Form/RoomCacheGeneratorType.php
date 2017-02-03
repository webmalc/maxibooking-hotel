<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
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

class RoomCacheGeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('begin', DateType::class, array(
                    'label' => 'Начало периода',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime('midnight'),
                    'required' => true,
                    'attr' => array('class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('end', DateType::class, array(
                    'label' => 'Конец периода',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => array('class' => 'datepicker end-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('weekdays',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                    'label' => 'Дни недели',
                    'required' => false,
                    'multiple' => true,
                    'choices' => $options['weekdays'],
                    'help' => 'Дни недели для которых будет произведена генерация наличия мест',
                    'attr' => array('placeholder' => 'Все дни недели'),
                ])
                ->add('roomTypes', DocumentType::class, [
                    'label' => 'Типы номеров',
                    'required' => true,
                    'multiple' => true,
                    'class' => 'MBHHotelBundle:RoomType',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                    'help' => 'Типы номеров для которых будет произведена генерация наличия мест',
                    'attr' => array('placeholder' => $options['hotel']. ': все типы номеров', 'class' => 'select-all'),
                ])
                ->add('quotas', CheckboxType::class, [
                    'label' => 'Установить квоты?',
                    'value' => true,
                    'required' => false,
                    'help' => 'Установить квоты номеров по тарифам?'
                ])
                ->add('tariffs', DocumentType::class, [
                    'label' => 'Тарифы',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->fetchChildTariffsQuery($options['hotel'], 'rooms');
                    },
                    'help' => 'Тарифы для которых будет произведена генерация квот',
                    'attr' => array('placeholder' => 'Квоты не будут сгенерированы'),
                ])
                ->add('rooms', TextType::class, [
                    'label' => 'Количество мест',
                    'required' => true,
                    'data' => null,
                    'attr' => ['class' => 'spinner--1 delete-rooms'],
                    'constraints' => [
                        new Range(['min' => -1, 'minMessage' => 'Количество мест не может быть меньше минус одного']),
                        new NotBlank()
                    ],
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvomest',
                ])
        ;
    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if($data['begin'] >= $data['end']){
            $context->addViolation('Начало периода должно быть меньше конца периода.');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370 ) {
            $context->addViolation('Период не может быть больше года.');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'constraints' => new Callback([$this, 'checkDates'])
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_room_cache_generator_type';
    }

}
