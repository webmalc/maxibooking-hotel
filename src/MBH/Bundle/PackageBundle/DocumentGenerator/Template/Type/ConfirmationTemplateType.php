<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ConfirmationTemplateType
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class ConfirmationTemplateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasFull', 'checkbox', [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasFull'
            ])
            ->add('hasServices', 'checkbox', [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasServices'
            ])
            ->add('hasStamp', 'checkbox', [
                'required' => false,
                'label' => 'templateDocument.form.confirmation.hasStamp'
            ]);

    }

    public function getName()
    {
        return 'confirmation_template';
    }
}