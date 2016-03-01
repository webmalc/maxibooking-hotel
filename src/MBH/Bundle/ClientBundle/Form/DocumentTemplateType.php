<?php

namespace MBH\Bundle\ClientBundle\Form;

use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * Class DocumentTemplateType
 *

 */
class DocumentTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', ['label' => 'Название'])
            ->add('content', 'textarea', [
                'label' => 'Шаблон',
                'attr' => ['rows' => 30]
            ])
            ->add('orientation', 'choice', [
                'label' => 'Ориентация', 'choices' => DocumentTemplate::getOrientations()
            ])
            ->add('hotel', 'document', [
                'label' => 'Отель',
                'class' => Hotel::class,
                'required' => false
            ])
            ->add('organization', 'document', [
                'label' => 'Организация',
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

    public function getName()
    {
        return 'mbh_client_document_template';
    }
}
