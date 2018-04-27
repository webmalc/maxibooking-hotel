<?php


namespace MBH\Bundle\PriceBundle\Form\Batch;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PriceBundle\Document\Tariff;
use Symfony\Component\Form\FormBuilderInterface;

class BatchSpecialTariffType extends AbstractBatchType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'tariff',
                DocumentType::class,
                [
                    'class' => Tariff::class,
                    'required' => true,
                    'label' => 'Тариф',
                    'attr' => [
                        'class' => 'form-control',

                    ],
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->fetchQueryBuilder($options['hotel']);
                    },
                ]
            );
        parent::buildForm($builder, $options);
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix(): ?string
    {
        return 'batch_tariff_apply';
    }
}