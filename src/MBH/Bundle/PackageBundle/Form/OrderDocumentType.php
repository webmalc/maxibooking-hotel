<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\BaseBundle\Form\ProtectedFileType;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class OrderDocumentType
 * @package MBH\Bundle\PackageBundle\Form
 */
class OrderDocumentType extends AbstractType
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';

    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mainGroupTitles = [
            self::SCENARIO_ADD => 'form.order_document_type.add_document',
            self::SCENARIO_EDIT => 'form.order_document_type.edit_document',
        ];

        $groupTitle = $mainGroupTitles[$options['scenario']];

        $builder->add(
            'type',  InvertChoiceType::class,
            [
                'group' => $groupTitle,
                'label' => 'form.order_document_type.type.label',
                'required' => true,
                'placeholder' => '',
                'choices' => $options['documentTypes']
            ]
        );

        $builder->add(
            'scanType',  InvertChoiceType::class,
            [
                'group' => $groupTitle,
                'label' => 'form.order_document_type.scan_type',
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
                'label' => 'form.order_document_type.client.label',
                'class' => 'MBHPackageBundle:Tourist',
                'required' => false,
                'choice_label' => function ($tourist) {
                    return $tourist->generateFullName() . ($tourist->getBirthday() ? ' (' . $tourist->getBirthday()->format('d.m.Y') . '), '
                            . $this->translator->trans('package.document_type.tourist_age')
                            . ': ' . $tourist->getAge() : '');
                },
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
            ProtectedFileType::class,
            [
                'group' => $groupTitle,
                'label' => $options['scenario'] == self::SCENARIO_EDIT ? 'form.order_document_type.change_file' : 'form.order_document_type.file',
                'required' => $options['scenario'] == self::SCENARIO_ADD,
            ] + ($options['scenario'] == self::SCENARIO_EDIT ? ['help' => '<i class="fa '.(isset($typeIcons[strtolower($document->getExtension())]) ? $typeIcons[strtolower($document->getExtension())] : null).'"></i> '.$document->getOriginalName()] : [])
        );

        $builder->add(
            'comment',
            TextareaType::class,
            [
                'group' => $groupTitle,
                'label' => 'form.order_document_type.note',
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
            'data_class' => OrderDocument::class
        ]);
    }
}