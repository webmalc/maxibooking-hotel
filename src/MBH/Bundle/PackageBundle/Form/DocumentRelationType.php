<?php

namespace MBH\Bundle\PackageBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class DocumentRelationType
 * @package MBH\Bundle\PackageBundle\Form
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class DocumentRelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $documentTypes = [];//include '/var/www/mbh/app/Resources/vega/docum.php';

        $group = 'DocumentRelation';

        $builder
            ->add('type', 'choice', [
                'choices' => $documentTypes,
                //'group' => $group,
            ])
            ->add('authority_organ', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaFMS',
                //'property_path' => 'code',
                //'group' => $group,
                'required' => false,
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
                'attr' => ['class' => 'input-small'],
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