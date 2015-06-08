<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AddressObjectDecomposedType
 * @package MBH\Bundle\PackageBundle\Form
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class AddressObjectDecomposedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('city', 'text', [
        ])
        ->add('district', 'document', [
            'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion'
        ])
        ->add('settlement', 'text', [
        ])
        ->add('urbanarea', 'text', [
        ])
        ->add('street', 'text', [
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