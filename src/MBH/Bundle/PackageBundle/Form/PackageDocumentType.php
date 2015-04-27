<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 22.04.15
 * Time: 20:14
 */

namespace MBH\Bundle\PackageBundle\Form;


use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\PackageDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;

class PackageDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            [
                'label' => 'Тип',
                'group' => 'Добавить документ',
                'required' => true,
                'choices' => $options['documentTypes']
            ]
        );

        $builder->add(
            'file',
            'file',
            [
                'group' => 'Добавить документ',
                'label' => 'Файл',
                'required' => true,
            ]
        );

        $touristIds = $options['touristIds'];

        $builder->add(
            'tourist',
            'document',
            [
                'group' => 'Добавить документ',
                'label' => 'Клиент',
                'class' => 'MBHPackageBundle:Tourist',
                'required' => false,
                //'choices' => $options['tourists']
                'query_builder' => function(DocumentRepository $er) use($touristIds) {
                    return $er->createQueryBuilder()->field('_id')->in($touristIds);
                },
            ]
        );

        $builder->add(
            'comment',
            'textarea',
            [
                'group' => 'Добавить документ',
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
        ]);
    }


}