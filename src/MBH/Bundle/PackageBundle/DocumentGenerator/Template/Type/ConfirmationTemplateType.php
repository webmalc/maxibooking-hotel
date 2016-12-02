<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ConfirmationTemplateType

 */
class ConfirmationTemplateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasFull', CheckboxType::class, [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasFull'
            ])
            ->add('hasServices', CheckboxType::class, [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasServices'
            ])
            ->add('hasStamp', CheckboxType::class, [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasStamp'
            ]);

    }

    public function getBlockPrefix()
    {
        return 'confirmation_template';
    }
}