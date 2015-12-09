<?php

namespace MBH\Bundle\CashBundle\Form;

use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            ->add('organizationPayer', 'text', [
                'label' => 'form.cashDocumentType.organization',
                'required' => false
            ])
            ->add('touristPayer', 'text', [
                'label' => 'form.cashDocumentType.tourist',
                'required' => false
            ])
            ->add('payer_select', 'hidden', [
                'required' => false,
                'mapped' => false,
            ])
        ;
        $builder->get('organizationPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Organization::class));
        $builder->get('touristPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Tourist::class));
    }
}