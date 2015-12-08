<?php

namespace MBH\Bundle\CashBundle\Form;

use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class NewCashDocumentType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class NewCashDocumentType extends CashDocumentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('organizationPayer', 'document', [
                'label' => 'form.cashDocumentType.organization',
                'required' => false,
                'class' => Organization::class
            ])
            ->add('touristPayer', 'document', [
                'label' => 'form.cashDocumentType.tourist',
                'required' => false,
                'class' => Tourist::class
            ])
            ->add('payer_select', 'hidden', [
                'required' => false,
                'mapped' => false,
            ])
        ;
    }
}