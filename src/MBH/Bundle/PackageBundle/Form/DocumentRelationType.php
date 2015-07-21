<?php

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\VegaBundle\Service\DictionaryProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DocumentRelationType
 * @package MBH\Bundle\PackageBundle\Form
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentRelationType extends AbstractType
{
    /**
     * @var DictionaryProvider
     */
    private $dictionaryProvider;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function setDictionaryProvider(DictionaryProvider $dictionaryProvider)
    {
        $this->dictionaryProvider = $dictionaryProvider;
    }

    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @todo replace documentTypes by VagaDocumentType
     * @see \MBH\Bundle\VegaBundle\Document\VegaDocumentType
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dictTypes = array_keys($this->dictionaryProvider->getDictTypes());
        $documentTypes = array_map(['\MBH\Bundle\VegaBundle\Service\FriendlyFormatter', 'convertDocumentType'], $this->dictionaryProvider->getDocumentTypes());

        asort($documentTypes);

        //main
        $builder
            ->add('citizenship', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'label' => 'form.TouristExtendedType.citizenship',
                'group' => 'form.DocumentRelation.citizenship',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                }
            ])
            ->add('type', 'choice', [
                'choices' => $documentTypes,
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.type',
                'property_path' => 'documentRelation.type'
            ])
            ->add('series', 'text', [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.series',
                'property_path' => 'documentRelation.series'
            ])
            ->add('number', 'text', [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.number',
                'property_path' => 'documentRelation.number',
            ])
            ->add('authorityOrgan', 'text', [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.authority',
                'required' => false,
                'property_path' => 'documentRelation.authorityOrgan'
            ])
            ->add('issued', 'date', [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.issued',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.issued'
            ])
            ->add('relation', 'choice', [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.relation',
                'choices' => array_combine($dictTypes, $dictTypes),
                'data' => 'owner',
                'property_path' => 'documentRelation.relation'
            ]);

        $builder->get('authorityOrgan')
            ->addModelTransformer(new EntityToIdTransformer($this->managerRegistry->getManager(), 'MBH\Bundle\VegaBundle\Document\VegaFMS'));

        //birthplace
        $builder
            ->add('country', 'document', [
                'group' => 'form.DocumentRelation.birthplace',
                'label' => 'form.BirthplaceType.country',
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'empty_value' => '',
                'required' => false,
                'property_path' => 'birthplace.country'
            ])
            ->add('main_region', 'text', [
                'group' => 'form.DocumentRelation.birthplace',
                'label' => 'form.BirthplaceType.main_region',
                'required' => false,
                'property_path' => 'birthplace.main_region',
                'attr' => ['class' => 'typeahead']
            ])
            ->add('district', 'text', [
                'group' => 'form.DocumentRelation.birthplace',
                /*'class' => 'MBH\Bundle\VegaBundle\Document\VegaRegion',
                'query_builder' => function(DocumentRepository $repository){
                    return $repository->createQueryBuilder()->sort(['name' => 1]);
                },
                'empty_value' => '',
                */
                'label' => 'form.BirthplaceType.district',
                'required' => false,
                'property_path' => 'birthplace.district'
            ])
            ->add('city', 'text', [//'mbh_city'
                'group' => 'form.DocumentRelation.birthplace',
                'label' => 'form.BirthplaceType.city',
                'required' => false,
                'property_path' => 'birthplace.city'
            ])
            ->add('settlement', 'text', [
                'group' => 'form.DocumentRelation.birthplace',
                'label' => 'form.BirthplaceType.settlement',
                'required' => false,
                'property_path' => 'birthplace.settlement'
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
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Tourist'
        ]);
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'mbh_document_relation';
    }
}