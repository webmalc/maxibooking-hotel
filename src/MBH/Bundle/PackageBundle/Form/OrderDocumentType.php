<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class OrderDocumentType
 * @package MBH\Bundle\PackageBundle\Form

 */
class OrderDocumentType extends AbstractType
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
            'type',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
            [
                'group' => $groupTitle,
                'label' => 'mbhpackagebundle.form.orderdocumenttype.tip',
                'required' => true,
                'placeholder' => '',
                'choices' => $options['documentTypes']
            ]
        );

        $builder->add(
            'scanType',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class,
            [
                'group' => $groupTitle,
                'label' => 'mbhpackagebundle.form.orderdocumenttype.tip.skana',
                'required' => false,
                'placeholder' => '',
                'choices' => $options['scanTypes']
            ]
        );

        $touristIds = $options['touristIds'];

        $builder->add(
            'tourist',
            DocumentType::class,
            [
                'group' => $groupTitle,
                'label' => 'mbhpackagebundle.form.orderdocumenttype.client',
                'class' => 'MBHPackageBundle:Tourist',
                'required' => false,
                'choice_label' => 'generateFullNameWithAge',
                'query_builder' => function(DocumentRepository $er) use($touristIds) {
                    return $er->createQueryBuilder()->field('_id')->in($touristIds);
                },
            ]
        );

        /** @var OrderDocument $document */
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
            FileType::class,
            [
                'group' => $groupTitle,
                'label' => $options['scenario'] == self::SCENARIO_EDIT ? 'mbhpackagebundle.form.orderdocumenttype.zamenit.fayl' : 'mbhpackagebundle.form.orderdocumenttype.file',
                'required' => $options['scenario'] == self::SCENARIO_ADD,
            ] + ($options['scenario'] == self::SCENARIO_EDIT ? ['help' => '<i class="fa '.(isset($typeIcons[strtolower($document->getExtension())]) ? $typeIcons[strtolower($document->getExtension())] : null).'"></i> '.$document->getOriginalName()] : [])
        );

        $builder->add(
            'comment',
            TextareaType::class,
            [
                'group' => $groupTitle,
                'label' => 'mbhpackagebundle.form.orderdocumenttype.comment',
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
    public function getBlockPrefix()
    {
        return 'mbh_package_bundle_order_document_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'documentTypes' => [],
            'scanTypes' => [],
            'touristIds' => [],
            'scenario' => self::SCENARIO_ADD,
            'document' => null,
        ]);
    }
}