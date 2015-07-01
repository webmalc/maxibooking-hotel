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
            ->add('country', 'text', [])
            ->add('city', 'text', [])
            ->add('zip_code', 'text', [])
            ->add('region', 'text', [])
            ->add('district', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion'
            ])
            ->add('settlement', 'text', [])
            ->add('urbanarea', 'text', [])
            ->add('street', 'text', [])
            ->add('house', 'text', [])
            ->add('corpus', 'text', [])
            ->add('flat', 'text', []);
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