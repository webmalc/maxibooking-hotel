<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoomsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['booking'] as $name => $label) {

            $builder->add($name, 'document', [
                'label' => $label,
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function(DocumentRepository $er) use($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb->field('hotel.id')->equals($options['hotel']->getId());
                    }
                    return $qb;
                },
                'empty_value' => '',
                'required' => false,
                'attr' => ['placeholder' => 'roomtype.placeholder'],
                'group' => isset($options['groupName']) ? $options['groupName'] : ''
            ]);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'constraints' => [new Callback(['methods' => [[$this,'check']]])],
                'booking' => [],
                'hotel' => null,
            ]
        );
    }

    public function check($data, ExecutionContextInterface $context)
    {
        $ids = [];
        foreach($data as $roomType) {
            if ($roomType && in_array($roomType->getId(), $ids)) {
                $context->addViolation('roomtype.validation');
            }
            if ($roomType) {
                $ids[] = $roomType->getId();
            }
        };
    }

    public function getName()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }

}
