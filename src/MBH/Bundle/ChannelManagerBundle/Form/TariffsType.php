<?php

namespace MBH\Bundle\ChannelManagerBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PriceBundle\Document\Tariff;
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
                'label' => $info['title'] . ' (ID: ' . $name . ')',
                'class' => 'MBHPriceBundle:Tariff',
                'query_builder' => function(DocumentRepository $er) use($options) {
                    $qb = $er->createQueryBuilder();
                    if ($options['hotel'] instanceof Hotel) {
                        $qb
                            ->field('hotel.id')->equals($options['hotel']->getId())
                            ->field('isEnabled')->equals(true)
                        ;
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
                'constraints' => null,
                'booking' => [],
                'hotel' => null,
            ]
        );
    }

    public function check($data, ExecutionContextInterface $context)
    {
        $ids = [];
        $notMappedTariffsIds = [];

        /** @var Tariff $tariff */
        foreach($data as $cmTariffId => $tariff) {
            if ($tariff && in_array($tariff->getId(), $ids)) {
                $context->addViolation('tarifftype.validation');
            }
            if ($tariff) {
                $ids[] = $tariff->getId();
            }
            if (is_null($tariff)) {
                $notMappedTariffsIds[] = $cmTariffId;
            }
        };

        if (!empty($notMappedTariffsIds)) {
            $context->addViolation('tarifftype.validation.not_all_tariffs_synced', ['%ids%' => join(', ', $notMappedTariffsIds)]);
        }
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_channelmanagerbundle_tariffs_type';
    }

}
