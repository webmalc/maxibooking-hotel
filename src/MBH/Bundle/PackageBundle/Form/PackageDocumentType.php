<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\PackageDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class PackageDocumentType
 * @package MBH\Bundle\PackageBundle\Form
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class PackageDocumentType extends AbstractType
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mainGroupTitles = [
            self::SCENARIO_ADD => 'Добавить документ',
            self::SCENARIO_EDIT => 'Редактировать документ',
        ];

        $groupTitle = $mainGroupTitles[$options['scenario']];

        $builder->add(
            'type',
            'choice',
            [
                'group' => $groupTitle,
                'label' => 'Тип',
                'required' => true,
                'choices' => $options['documentTypes']
            ]
        );

        $touristIds = $options['touristIds'];

        $builder->add(
            'tourist',
            'document',
            [
                'group' => $groupTitle,
                'label' => 'Клиент',
                'class' => 'MBHPackageBundle:Tourist',
                'required' => false,
                'property' => 'generateFullNameWithAge',
                //'choices' => $options['tourists']
                'query_builder' => function(DocumentRepository $er) use($touristIds) {
                    return $er->createQueryBuilder()->field('_id')->in($touristIds);
                },
            ]
        );

        /** @var PackageDocument $document */
        $document = $options['document'];

        $typeIcons = [
            'doc' => 'fa-file-word-o',
            'pdf' => 'fa-file-pdf-o',
            'jpg' => 'fa-file-image-o',
            'jpeg' => 'fa-file-image-o',
            'png' => 'fa-file-image-o',
            'xls' => 'fa-file-excel-o'
        ];
        $builder->add(
            'file',
            'file',
            [
                'group' => $groupTitle,
                'label' => $options['scenario'] == self::SCENARIO_EDIT ? 'Заменить файл' : 'Файл',
                'required' => $options['scenario'] == self::SCENARIO_ADD,
            ] + ($options['scenario'] == self::SCENARIO_EDIT ? ['help' => '<i class="fa '.$typeIcons[$document->getExtension()].'"></i> '.$document->getOriginalName()] : [])
        );

        $builder->add(
            'comment',
            'textarea',
            [
                'group' => $groupTitle,
                'label' => 'Комментарий',
                'required' => false,
                'constraints' => [
                    new Length(['min' => 2, 'max' => 300])
                ]
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_document_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'documentTypes' => [],
            'touristIds' => [],
            'scenario' => self::SCENARIO_ADD,
            'document' => null,
        ]);
    }
}