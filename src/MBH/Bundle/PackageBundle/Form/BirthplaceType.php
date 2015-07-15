<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class BirthplaceType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class BirthplaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', 'document', [
                'label' => 'form.BirthplaceType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'empty_value' => ''
            ])
            ->add('city', 'text', [//'mbh_city'
                'label' => 'form.BirthplaceType.city',
            ])
            ->add('main_region', 'text', [
                'label' => 'form.BirthplaceType.main_region',
            ])
            ->add('district', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.BirthplaceType.district',
                'empty_value' => ''
            ])
            ->add('settlement', 'text', [
                'label' => 'form.BirthplaceType.settlement',
            ]);
    }
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'mbh_birthplace';
    }
}