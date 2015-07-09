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
        $group = 'DocumentRelation';

        $builder
            ->add('type', 'choice', [
                'choices' => $this->dictionaryProvider->getDocumentTypes(),
                //'group' => $group,
                'required' => false,
                'empty_value' => ''
            ])
            ->add('authority_organ', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaFMS',
                //'property_path' => 'code',
                //'group' => $group,
                'required' => false,
                'empty_value' => ''
            ])
            ->add('authority', 'text', [
                //'group' => $group,
                'required' => false,
            ])
            ->add('series', 'text', [
                //'group' => $group,
                'required' => false,
            ])
            ->add('number', 'text', [
                //'group' => $group,
                'required' => false,
            ])
            ->add('issued', 'date', [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                //'group' => $group,
                'required' => false,
                'attr' => ['class' => 'input-small', 'data-date-format' => 'dd.mm.yyyy'],
            ])
            ->add('relation', 'choice', [
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