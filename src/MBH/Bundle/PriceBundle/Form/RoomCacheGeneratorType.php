<?php

namespace MBH\Bundle\PriceBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PriceBundle\Form\Partial\PreRedirectParams;
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

    use PreRedirectParams;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('begin', DateType::class, array(
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.nachaloperioda',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => $this->getBeginDate($options),
                    'required' => true,
                    'attr' => array('class' => 'datepicker begin-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('end', DateType::class, array(
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.konetsperioda',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'data' => $this->getEndDate($options),
                    'required' => true,
                    'attr' => array('class' => 'datepicker end-datepicker', 'data-date-format' => 'dd.mm.yyyy'),
                    'constraints' => [new NotBlank(), new Date()],
                ))
                ->add('weekdays',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.dninedeli',
                    'required' => false,
                    'multiple' => true,
                    'choices' => $options['weekdays'],
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.dninedelidlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                    'attr' => array('placeholder' => 'mbhpricebundle.form.roomcachegeneratortype.vse.dni.nedeli'),
                ])
                ->add('roomTypes', DocumentType::class, [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.tipynomerov',
                    'required' => true,
                    'multiple' => true,
                    'class' => 'MBHHotelBundle:RoomType',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.tipynomerovdlyagotorykhbudetproizvedenageneratsiyanalichiyamest',
                    'attr' => array('placeholder' => $options['hotel']. ': mbhpricebundle.form.roomcachegeneratortype.vse.tipy.nomerov', 'class' => 'select-all'),
                ])
                ->add('quotas', CheckboxType::class, [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.ustanovitkvoty',
                    'value' => true,
                    'required' => false,
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.ustanovitkvotynomerovpotarifam'
                ])
                ->add('tariffs', DocumentType::class, [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.tarify',
                    'required' => false,
                    'multiple' => true,
                    'class' => 'MBHPriceBundle:Tariff',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->fetchChildTariffsQuery($options['hotel'], 'rooms');
                    },
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.tarifydlyagotorykhbudetproizvedenageneratsiyakvot',
                    'attr' => array('placeholder' => 'mbhpricebundle.form.roomcachegeneratortype.kvoty.ne.budut.sgenerirovany'),
                ])
                ->add('rooms', TextType::class, [
                    'label' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvo.mest',
                    'required' => true,
                    'data' => null,
                    'attr' => ['class' => 'spinner--1 delete-rooms'],
                    'constraints' => [
                        new Range(['min' => -1, 'minMessage' => 'mbhpricebundle.room_cache_generator_type.number_of_places_cannot_be_less_then_one']),
                        new NotBlank()
                    ],
                    'help' => 'mbhpricebundle.form.roomcachegeneratortype.kolichestvomest',
                ])
        ;
    }

    public function checkDates($data, ExecutionContextInterface $context)
    {
        if($data['begin'] >= $data['end']){
            $context->addViolation('mbhpricebundle.room_cache_generator_type.begin_cannot_be_more_end');
        }
        if ($data['end']->diff($data['begin'])->format("%a") > 370 ) {
            $context->addViolation('mbhpricebundle.room_cache_generator_type.period_length_cannot_be_more_then_year');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'weekdays' => [],
            'hotel' => null,
            'constraints' => new Callback([$this, 'checkDates']),
            'preRedirectFormData' => ['begin' => null, 'end' => null]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_room_cache_generator_type';
    }

}
