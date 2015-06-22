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

class RoomCacheGeneratorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('begin', 'date', array(
                    'label' => 'Начало периода',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime('midnight'),
                    'required' => true,
                    'attr' => array('class' => 'datepicker begin-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('end', 'date', array(
                    'label' => 'Конец периода',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => array('class' => 'datepicker end-datepiker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('weekdays', 'choice', [
                    'label' => 'Дни недели',
                    'required' => false,
                    'multiple' => true,
                    'choices' => $options['weekdays'],
                    'help' => 'Дни недели для готорых будет произведена генерация наличия мест',
                    'attr' => array('placeholder' => 'Все дни недели'),
                ])
                ->add('roomTypes', 'document', [
                    'label' => 'Типы номеров',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'MBHHotelBundle:RoomType',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                    'help' => 'Типы номеров для готорых будет произведена генерация наличия мест',
                    'attr' => array('placeholder' => $options['hotel']. ': все типы номеров'),
                ])
                ->add('tariffs', 'document', [
                    'label' => 'Тарифы',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                    'help' => 'Тарифы для готорых будет произведена генерация квот',
                    'attr' => array('placeholder' => 'Квоты не будут сгенерированы'),
                ])
                ->add('rooms', 'text', [
                    'label' => 'Количество мест',
                    'required' => true,
                    'data' => null,
                    'attr' => ['class' => 'spinner--1 delete-rooms'],
                    'constraints' => [
                        new Range(['min' => -1, 'minMessage' => 'Количество мест не может быть меньше минус одного']),
                        new NotBlank()
                    ],
                    'help' => 'Количестов мест доступные в выбранные сроки. Минус один (-1) для удаления дней',
                ])
                /*->add('isClosed', 'checkbox', [
                    'label' => 'Закрыто?',
                    'value' => true,
                    'required' => false,
                ])*/
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
        return 'mbh_bundle_pricebundle_room_cache_generator_type';
    }

}
