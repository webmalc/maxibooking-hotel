<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressObjectDecomposedType
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class AddressObjectDecomposedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', 'document', [
                'label' => 'form.AddressObjectDecomposedType.country',
                'class' => 'MBH\Bundle\HotelBundle\Document\Country'
            ])
            ->add('city', 'text', [
                'label' => 'form.AddressObjectDecomposedType.city',
            ])
            ->add('zip_code', 'text', [
                'label' => 'form.AddressObjectDecomposedType.zip_code',
            ])
            ->add('region', 'text', [
                'label' => 'form.AddressObjectDecomposedType.region',
            ])
            /*->add('district', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.AddressObjectDecomposedType.district',
                'empty_value' => ''
            ])*/
            ->add('settlement', 'text', [
                'label' => 'form.AddressObjectDecomposedType.settlement',
            ])
            ->add('urbanarea', 'text', [
                'label' => 'form.AddressObjectDecomposedType.urbanarea',
            ])
            ->add('street', 'text', [
                'label' => 'form.AddressObjectDecomposedType.street',
            ])
            ->add('house', 'text', [
                'label' => 'form.AddressObjectDecomposedType.house',
            ])
            ->add('corpus', 'text', [
                'label' => 'form.AddressObjectDecomposedType.corpus',
            ])
            ->add('flat', 'text', [
                'label' => 'form.AddressObjectDecomposedType.flat',
            ]);
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