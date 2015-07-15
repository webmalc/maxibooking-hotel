<?php

namespace MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\VegaBundle\Services\DictionaryProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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
            ->add('type', 'choice', [
                'choices' => $this->dictionaryProvider->getDocumentTypes(),
                //'group' => $group,
                'label' => 'form.DocumentRelation.type',
                'required' => false,
                'empty_value' => ''
            ])
            ->add('authority_organ', 'text',/*'document',*/ [
                //'class' => 'MBH\Bundle\VegaBundle\Document\VegaFMS',
                //'empty_value' => ''
                'label' => 'form.DocumentRelation.authority_organ',
                //'property_path' => 'code',
                //'group' => $group,
                'required' => false,
                'mapped' => false,
            ])
            ->add('authority', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.authority',
            ])
            ->add('series', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.series',
            ])
            ->add('number', 'text', [
                //'group' => $group,
                'required' => false,
                'label' => 'form.DocumentRelation.number',
            ])
            ->add('issued', 'date', [
                'label' => 'form.DocumentRelation.issued',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                //'group' => $group,
                'required' => false,
                'attr' => ['class' => 'input-small', 'data-date-format' => 'dd.mm.yyyy'],
            ])
            ->add('relation', 'choice', [
                'label' => 'form.DocumentRelation.relation',
                'choices' => [
                    'ВЛАДЕЛИЦ',
                    'ВПИСАН',
                ],
                //'group' => $group,
                'required' => false,
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