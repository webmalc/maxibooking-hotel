<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressObjectDecomposedType
 *

 */
class AddressObjectDecomposedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', 'document', [
                'label' => 'form.AddressObjectDecomposedType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'query_builder' => function(DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'required' => false,
            ])
            ->add('region', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.AddressObjectDecomposedType.region',
                'query_builder' => function(DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'required' => false,
            ])
            ->add('city', 'text', [
                'label' => 'form.AddressObjectDecomposedType.city',
                'required' => false,
            ])
            ->add('settlement', 'text', [
                'label' => 'form.AddressObjectDecomposedType.settlement',
                'required' => false,
            ])
            ->add('district', 'text', [
                'label' => 'form.AddressObjectDecomposedType.district',
                'required' => false,
            ])
            /*->add('urbanarea', 'text', [
                'label' => 'form.AddressObjectDecomposedType.urbanarea',
                'required' => false,
            ])*/
            ->add('street', 'text', [
                'label' => 'form.AddressObjectDecomposedType.street',
                'required' => false,
            ])
            ->add('house', 'text', [
                'label' => 'form.AddressObjectDecomposedType.house',
                'required' => false,
            ])
            ->add('corpus', 'text', [
                'label' => 'form.AddressObjectDecomposedType.corpus',
                'required' => false,
            ])
            ->add('flat', 'text', [
                'label' => 'form.AddressObjectDecomposedType.flat',
                'required' => false,
            ])
            ->add('zip_code', 'text', [
                'label' => 'form.AddressObjectDecomposedType.zip_code',
                'required' => false,
            ])
            ->add('address_object', 'text', [
                'label' => 'form.TouristExtendedType.address_object',
                'required' => false,
                'help' => 'Передача адреса в виде кода (AOID) Федеральной информационной адресной системы (ФИАС)'
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
    public function getName()
    {
        return 'mbh_address_object_decomposed';
    }

}