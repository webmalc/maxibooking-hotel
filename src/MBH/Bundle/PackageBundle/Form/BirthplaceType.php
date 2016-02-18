<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BirthplaceType

 */
class BirthplaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', 'document', [
                'label' => 'form.BirthplaceType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'empty_value' => '',
                'required' => false,
            ])
            ->add('city', 'text', [//'mbh_city'
                'label' => 'form.BirthplaceType.city',
                'required' => false,
            ])
            ->add('main_region', 'text', [
                'label' => 'form.BirthplaceType.main_region',
                'required' => false,
            ])
            ->add('district', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.BirthplaceType.district',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'empty_value' => '',
                'required' => false,
            ])
            ->add('settlement', 'text', [
                'label' => 'form.BirthplaceType.settlement',
                'required' => false,
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
            'data_class' => 'MBH\Bundle\PackageBundle\Document\BirthPlace'
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