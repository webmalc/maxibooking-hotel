<?php


namespace MBH\Bundle\PriceBundle\Form\Batch;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\PriceBundle\Document\Promotion;
use Symfony\Component\Form\FormBuilderInterface;

class BatchSpecialPromotionType extends AbstractBatchType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'promotion',
                DocumentType::class,
                [
                    'class' => Promotion::class,
                    'required' => true,
                    'label' => 'Акция',
                    'attr' => [
                        'class' => 'form-control',

                    ],
                ]
            );
        parent::buildForm($builder, $options);
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix(): ?string
    {
        return 'batch_promotion_apply';
    }

}