<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TouristMigrationType
 */
class TouristMigrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('series', TextType::class, [
                'label' => 'tourist.migration.type_series',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('number', TextType::class, [
                'label' => 'tourist.migration.type_number',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('representative', TextareaType::class, [
                'label' => 'tourist.migration.type_representative',
                'group' => 'migration.card',
                'required' => false,
            ])
            ->add('region', DocumentType::class, [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.AddressObjectDecomposedType.region',
                'query_builder' => function(DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'group' => 'tourist.migration.type_address.group',
                'required' => false,
                'property_path' => 'addressObjectDecomposed.region'
            ])
            ->add('city', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.city',
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'property_path' => 'addressObjectDecomposed.city'
            ])
            ->add('settlement', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.settlement',
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'property_path' => 'addressObjectDecomposed.settlement'
            ])
            ->add('street', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.street',
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'property_path' => 'addressObjectDecomposed.street'
            ])
            ->add('house', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.house',
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'property_path' => 'addressObjectDecomposed.house'
            ])
            ->add('corpus', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.corpus',
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'property_path' => 'addressObjectDecomposed.corpus'
            ])
            ->add('structure', TextType::class, [
                'required' => false,
                'group' => 'tourist.migration.type_address.group',
                'attr' => [
                    'class' => 'spinner',
                ],
                'label' => 'form.AddressObjectDecomposedType.structure.label',
                'property_path' => 'addressObjectDecomposed.structure'
            ])
            ->add('flat', TextType::class, [
                'group' => 'tourist.migration.type_address.group',
                'label' => 'form.AddressObjectDecomposedType.flat',
                'required' => false,
                'property_path' => 'addressObjectDecomposed.flat'
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Migration'
        ]);
    }


    public function getBlockPrefix()
    {
        return 'mbh_package_tourist_migration';
    }
}