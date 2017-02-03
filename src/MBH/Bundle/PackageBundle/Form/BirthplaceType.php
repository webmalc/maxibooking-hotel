<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('country', DocumentType::class, [
                'label' => 'form.BirthplaceType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'placeholder' => '',
                'required' => false,
            ])
            ->add('city', TextType::class, [//'mbh_city'
                'label' => 'form.BirthplaceType.city',
                'required' => false,
            ])
            ->add('main_region', TextType::class, [
                'label' => 'form.BirthplaceType.main_region',
                'required' => false,
            ])
            ->add('district', DocumentType::class, [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'label' => 'form.BirthplaceType.district',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'placeholder' => '',
                'required' => false,
            ])
            ->add('settlement', TextType::class, [
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
    public function getBlockPrefix()
    {
        return 'mbh_birthplace';
    }
}