<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 04.06.15
 * Time: 17:35
 */

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormBuilderInterface;

class BirthplaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$builder->setCompound(true);
        $builder->setDataMapper(
            new PropertyPathMapper()
        );*/

        $builder
            ->add('country', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'empty_value' => ''
            ])
            ->add('city', 'mbh_city', [])
            ->add('main_region', 'text', [
            ])
            ->add('district', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'empty_value' => ''
            ])
            ->add('city', 'text', [
            ])
            ->add('settlement', 'text', [
            ])
        ;
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