<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DocumentTemplateType
 *

 */
class DocumentTemplateType extends AbstractType
{
    public const FORM_NAME = 'mbh_client_document_template';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, ['label' => 'mbhclientbundle.form.documenttemplatetype.nazvaniye'])
            ->add('content', TextareaType::class, [
                'label' => 'mbhclientbundle.form.documenttemplatetype.shablon',
                'attr' => ['rows' => 30]
            ])
            ->add('orientation',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'mbhclientbundle.form.documenttemplatetype.oriyentatsiya', 'choices' => DocumentTemplate::getOrientations()
            ])
            ->add('hotel', DocumentType::class, [
                'label' => 'form.documentTemplateType.hotel',
                'class' => Hotel::class,
                'required' => false
            ])
            ->add('organization', DocumentType::class, [
                'label' => 'mbhclientbundle.form.documenttemplatetype.organizatsiya',
                'class' => Organization::class,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\DocumentTemplate'
        ]);
    }

    public function getBlockPrefix()
    {
        return self::FORM_NAME;
    }
}
