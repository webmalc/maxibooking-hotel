<?php

namespace MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\VegaBundle\Services\DictionaryProvider;
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

    public function setDictionaryProvider(DictionaryProvider $dictionaryProvider)
    {
        $this->dictionaryProvider = $dictionaryProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('citizenship', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'label' => 'form.TouristExtendedType.citizenship',
                'group' => 'form.touristType.general_info',
                'empty_value' => ''
            ])
            ->add('type', 'choice', [
                'choices' => $this->dictionaryProvider->getDocumentTypes(),
                //'group' => $group,
                'label' => 'form.DocumentRelation.type',
                'required' => false,
                'empty_value' => '',
                'property_path' => 'documentRelation.type'
            ])
            ->add('authorityOrgan', 'text',/*'document',*/ [
                //'class' => 'MBH\Bundle\VegaBundle\Document\VegaFMS',
                //'empty_value' => ''
                'label' => 'form.DocumentRelation.authority_organ',
                //'property_path' => 'code',
                //'group' => $group,
                'required' => false,
                'mapped' => false,
                'property_path' => 'documentRelation.authorityOrgan'
            ])
            ->add('authority', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.authority',
                'property_path' => 'documentRelation.authority'
            ])
            ->add('series', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.series',
                'property_path' => 'documentRelation.series'
            ])
            ->add('number', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.number',
                'property_path' => 'documentRelation.number',
            ])
            ->add('issued', 'date', [
                'label' => 'form.DocumentRelation.issued',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                //'group' => $group,
                'required' => false,
                'attr' => ['class' => 'input-small', 'data-date-format' => 'dd.mm.yyyy'],
                'property_path' => 'documentRelation.issued'
            ])
            ->add('relation', 'choice', [
                'label' => 'form.DocumentRelation.relation',
                'choices' => [
                    'ВЛАДЕЛИЦ',
                    'ВПИСАН',
                ],
                //'group' => $group,
                'required' => false,
                'property_path' => 'documentRelation.relation'
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