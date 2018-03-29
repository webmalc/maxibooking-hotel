<?php


namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Form\DataTransformer\SpecialsToStringTransformer;
use MBH\Bundle\PriceBundle\Lib\SpecialBatcherHolder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchSpecialPromotionApplyType extends AbstractType
{

    /** @var DocumentManager */
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'promotion',
                DocumentType::class,
                [
                    'class' => Promotion::class,
                    'required' => true,
                    'label' => 'Акция',
                ]
            )
            ->add(
                'specials',
                TextType::class,
                [
                    'attr' => [
                        'class' => 'special-input'
                    ]
                ]
            );

        $builder->get('specials')
            ->addModelTransformer(new SpecialsToStringTransformer($this->dm));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => SpecialBatcherHolder::class,
                ]
            );
    }

    public function getBlockPrefix()
    {
        return 'batch_promotion_apply';
    }

}