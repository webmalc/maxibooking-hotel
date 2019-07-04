<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MBH\Bundle\ClientBundle\Lib\FMSDictionaries;
use MBH\Bundle\VegaBundle\Service\DictionaryProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;

/**
 * Class DocumentRelationType
 * @package MBH\Bundle\PackageBundle\Form
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

    /** @var FMSDictionaries */
    private $fmsDictionaries;

    public function __construct(DictionaryProvider $dictionaryProvider, ManagerRegistry $managerRegistry, FMSDictionaries $fmsDictionaries) {
        $this->dictionaryProvider = $dictionaryProvider;
        $this->managerRegistry = $managerRegistry;
        $this->fmsDictionaries = $fmsDictionaries;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dictTypes = $this->dictionaryProvider->getDictTypes();
        $passportDocTypeId = 103008;

        //main
        if ($options['citizenship']) {
            $builder
                ->add('citizenshipTld', TextType::class, [
                    'label' => 'form.TouristExtendedType.citizenship',
                    'group' => 'form.DocumentRelation.citizenship',
                    'attr' => [
                        'class' => 'billing-text-select',
                        'data-endpoint-name' => 'countries'
                    ],
                ]);
        }
        $builder
            ->add('type', InvertChoiceType::class, [
                'choices' => $this->fmsDictionaries->getDocumentTypes(),
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.type',
                'property_path' => 'documentRelation.type',
                'data' => $builder->getData()->getDocumentRelation()->getType() ?? $passportDocTypeId
            ])
            ->add('series', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.series',
                'property_path' => 'documentRelation.series'
            ])
            ->add('number', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'required' => false,
                'label' => 'form.DocumentRelation.number',
                'property_path' => 'documentRelation.number',
                'constraints' => [
                    new Type(['type' => 'numeric'])
                ]
            ]);

        $builder
            ->add('authorityOrganId', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.authority',
                'help' => 'form.DocumentRelation.authority.help',
                'required' => false,
                'property_path' => 'documentRelation.authorityOrganId',
                'attr' => [
                    'class' => 'billing-text-select',
                    'data-endpoint-name' => 'fms'
                ],
            ])
            ->add('authorityOrganText', TextType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.authority',
                'required' => false,
                'property_path' => 'documentRelation.authorityOrganText'
            ])
        ;

        $builder
            ->add('issued', DateType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.issued',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.issued'
            ])
            ->add('expiry', DateType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.expiry',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => ['class' => 'input-small datepicker', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.expiry'
            ])
            ->add('relation', InvertChoiceType::class, [
                'group' => 'form.DocumentRelation.main',
                'label' => 'form.DocumentRelation.relation',
                'choices' => array_combine($dictTypes, $dictTypes),
                'expanded' => true,
                'property_path' => 'documentRelation.relation'
            ]);

        if ($options['birthplace']) {
            $builder
                ->add('countryTld', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.country',
                    'required' => false,
                    'property_path' => 'birthplace.countryTld',
                    'attr' => [
                        'class' => 'billing-text-select',
                        'data-endpoint-name' => 'countries'
                    ],
                ])
                ->add('main_region', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.main_region',
                    'required' => false,
                    'property_path' => 'birthplace.main_region',
                    'attr' => ['class' => 'typeahead']
                ])
                ->add('district', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.district',
                    'required' => false,
                    'property_path' => 'birthplace.district'
                ])
                ->add('city', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.city',
                    'required' => false,
                    'property_path' => 'birthplace.city'
                ])
                ->add('settlement', TextType::class, [
                    'group' => 'form.DocumentRelation.birthplace',
                    'label' => 'form.BirthplaceType.settlement',
                    'required' => false,
                    'property_path' => 'birthplace.settlement'
                ]);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Tourist',
            //'cascade_validation' => true
            'citizenship' => true,
            'birthplace' => true,
        ]);
    }


    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'mbh_document_relation';
    }
}
