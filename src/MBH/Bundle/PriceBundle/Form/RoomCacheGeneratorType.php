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
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.nachaloperioda',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => new \DateTime('midnight'),
                    'required' => true,
                    'attr' => array('class' => 'datepicker begin-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('end', 'date', array(
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.konetsperioda',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'attr' => array('class' => 'datepicker end-datepicker input-remember', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('weekdays', 'choice', [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.dninedeli',
                    'required' => false,
                    'multiple' => true,
                    'choices' => $options['weekdays'],
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.dninedelidlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                    'attr' => array('placeholder' => 'mbhpricebundle.form.roomcachegeneratortype.vsedninedeli'),
                ])
                ->add('roomTypes', 'document', [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.tipynomerov',
                    'required' => true,
                    'multiple' => true,
                    'class' => 'MBHHotelBundle:RoomType',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.tipynomerovdlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                    'attr' => array('placeholder' => $options['hotel']. ': все типы номеров', 'class' => 'select-all'),
                ])
                ->add('quotas', 'checkbox', [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.ustanovitʹkvoty?',
                    'value' => true,
                    'required' => false,
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.ustanovitʹkvotynomerovpotarifam?'
                ])
                ->add('tariffs', 'document', [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.tarify',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->fetchChildTariffsQuery($options['hotel'], 'rooms');
                    },
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.tarifydlyagotorykhbudetproizvedenageneratsiyakvot',
                    'attr' => array('placeholder' => 'mbhpricebundle.form.roomcachegeneratortype.kvotynebudutsgenerirovany'),
                ])
                ->add('rooms', 'text', [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvomest',
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
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.zakryto?',
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
