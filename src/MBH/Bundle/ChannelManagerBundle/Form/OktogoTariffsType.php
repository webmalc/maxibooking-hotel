<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class OktogoTariffsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('data', HiddenType::class, [
            'label' =>false ,
            'data' => serialize($options['oktogo']),
        ]);
        foreach ($options['oktogo'] as $roomId => $tariffIds){
            foreach ($tariffIds as $tariffId => $tariffInfo){
                $builder->add($tariffInfo['rate_id'], 'document', [
                    'label' => $tariffInfo['title'],
                    'class' => 'MBHPriceBundle:Tariff',
                    'query_builder' => function(DocumentRepository $er) use($options) {
                        $qb = $er->createQueryBuilder();
                        if ($options['hotel'] instanceof Hotel) {
                            $qb->field('hotel.id')->equals($options['hotel']->getId());
                        }
                        return $qb;
                    },
                    'empty_value' => '',
                    'required' => false,
                    'group' => $tariffInfo['roomName'],
                    'attr' => ['placeholder' => 'tarifftype.placeholder']
                ]);

            }

        }
//        foreach ($options['oktogo'] as $tariff ) {
//
//            if ( !isset($tariff['rooms']  )== $tariff['rooms'] )
//            {
//
//            $builder->add($tariff['rate_id'] ,TextType::class, [
//                'label' =>$tariff['roomName'] ,
//            ]);
//
//            }
//
//            $builder->add($tariff['rate_id'], 'document', [
//                'label' => $tariff['title'],
//                'class' => 'MBHPriceBundle:Tariff',
//                'query_builder' => function(DocumentRepository $er) use($options) {
//                    $qb = $er->createQueryBuilder();
//                    if ($options['hotel'] instanceof Hotel) {
//                        $qb->field('hotel.id')->equals($options['hotel']->getId());
//                    }
//                    return $qb;
//                },
//
//                'empty_value' => '',
//                'required' => false,
////                'group_by' => 'roomTypeId',
//                'attr' => ['placeholder' => 'tarifftype.placeholder']
//            ]);
//        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                //'constraints' => [new Callback(['methods' => [[$this,'check']]])],
                'oktogo' => [],
                'hotel' => null,
            ]
        );
    }

    public function check($data, ExecutionContextInterface $context)
    {
        $ids = [];
        foreach($data as $tariff) {
            if ($tariff && in_array($tariff->getId(), $ids)) {
                $context->addViolation('tarifftype.validation');
            }
            if ($tariff) {
                $ids[] = $tariff->getId();
            }
        };
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_oktogo_type';
    }

}
