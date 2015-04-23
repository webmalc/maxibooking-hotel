<?php
/**
 * Created by PhpStorm.
 * User: mb
 * Date: 22.04.15
 * Time: 20:14
 */

namespace MBH\Bundle\PackageBundle\Form;


use MBH\Bundle\PackageBundle\Document\PackageDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackageDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            [
                'label' => 'Тип',
                'required' => true,
                'choices' => $options['documentTypes']
            ]
        );

        $builder->add(
            'file',
            'file',
            [
                'label' => 'Файл',
                'required' => true,
            ]
        );

        $builder->add(
            'comment',
            'textarea',
            [
                'label' => 'Комментарий',
                'required' => false,
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
            'documentTypes' => []
        ]);
    }


}