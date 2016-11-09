<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TariffsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['booking'] as $name => $info) {

            $builder->add($name, DocumentType::class, [
                'label' => $info['title'],
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function(DocumentRepository $er) use($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb->field('hotel.id')->equals($options['hotel']->getId());
                    }
                    return $qb;
                },
                'placeholder' => '',
                'required' => false,
                'attr' => ['placeholder' => 'tarifftype.placeholder']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                //'constraints' => [new Callback(['methods' => [[$this,'check']]])],
                'booking' => [],
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

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_booking_type';
    }

}
