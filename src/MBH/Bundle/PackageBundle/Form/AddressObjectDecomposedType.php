<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressObjectDecomposedType
 */
class AddressObjectDecomposedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', DocumentType::class, [
                'label' => 'form.AddressObjectDecomposedType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'query_builder' => function(DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'required' => false,
            ])
            ->add('region', DocumentType::class, [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.AddressObjectDecomposedType.region',
                'query_builder' => function(DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.city',
                'required' => false,
            ])
            ->add('settlement', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.settlement',
                'required' => false,
            ])
            ->add('district', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.district',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.street',
                'required' => false,
            ])
            ->add('house', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.house',
                'required' => false,
            ])
            ->add('corpus', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.corpus',
                'required' => false,
            ])
            ->add('flat', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.flat',
                'required' => false,
            ])
            ->add('zip_code', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.zip_code',
                'required' => false,
            ])
            ->add('address_object', TextType::class, [
                'label' => 'form.TouristExtendedType.address_object',
                'required' => false,
                'help' => 'form.AddressObjectDecomposedType.address_object.help'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'mbh_address_object_decomposed';
    }

}