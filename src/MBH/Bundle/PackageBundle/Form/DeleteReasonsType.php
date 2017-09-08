<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\DeleteReasonCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteReasonsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hotel = $options['hotel'];
        $builder
            ->add('fullTitle', TextType::class, [
                'label' => 'form.deleteReasonsType.name',
                'group' => 'form.deleteReasonsType.add_source',
                'required' => true,
                'attr' => ['placeholder' => 'form.deleteReasonsType.adds']
            ])
            ->add('category', DocumentType::class, [
                'label' => 'form.deleteReasonsType.category',
                'group' => 'form.deleteReasonsType.add_source',
                'class' => DeleteReasonCategory::class,
                'query_builder' => function ($dr) use ($hotel){
                /** @var DocumentRepository $dr */
                    $qb = $dr->createQueryBuilder();
                    if ($hotel) {
                        $qb->field('hotel')->references($hotel);
                    }
                    return $qb;
                }
            ])
            ->add('isDefault', CheckboxType::class, [
                'label' => 'form.deleteReasonsType.default',
                'group' => 'form.deleteReasonsType.add_source',
                'required' => false
            ])
            ->add('isEnabled', CheckboxType::class, [
                'label' => 'form.deleteReasonsType.isActive',
                'group' => 'form.deleteReasonsType.add_source',
                'required' => false,
                'value' => true,
                'data' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
            'hotel' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_packagepackagesourecetype';
    }

}
