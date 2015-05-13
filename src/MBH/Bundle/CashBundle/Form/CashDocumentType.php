<?php

namespace MBH\Bundle\CashBundle\Form;

use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CashDocumentType extends AbstractType
{
    private $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $payers = [];
        foreach ($options['payers'] as $payer) {
            $text = $payer->getName();
            if ($payer instanceof Organization) {
                $prefix = 'org';
                $text .= ' (ИНН '.$payer->getInn().') '.$payer->getDirectorFio();
            } elseif ($payer instanceof Tourist) {
                $prefix = 'tourist';
                $text .= $payer->getBirthday() ? ' '.$payer->getBirthday()->format('d.m.Y') : '';
            } else {
                throw new \Exception();
            }

            $payers[$prefix . '_' . $payer->getId()] = $text;
        }

        $builder
            ->add('payer_select', 'choice', [
                'label' => 'form.cashDocumentType.payer',
                'required' => true,
                'mapped' => false,
                'data' => $options['payer'] ? $options['payer'] : null,
                'group' => $options['groupName'],
                'choices' => $payers,
                'attr' => [
                    'placeholder' => 'form.cashDocumentType.placeholder_fio',
                    'style' => 'm  in-width: 500px',
                ]
            ])
            ->add('organizationPayer', 'hidden', [
                'label' => 'form.cashDocumentType.operation_type',
                'required' => false,
            ])
            ->add('touristPayer', 'hidden', [
                'label' => 'form.cashDocumentType.operation_type',
                'required' => false,
            ])
            ->add('operation', 'choice', [
                'label' => 'form.cashDocumentType.operation_type',
                'required' => true,
                'multiple' => false,
                'empty_value' => '',
                'group' => $options['groupName'],
                'choices' => $options['operations']
            ])
            ->add('total', 'text', [
                'label' => 'form.cashDocumentType.sum',
                'required' => true,
                'group' => $options['groupName'],
                'attr' => ['class' => 'price-spinner'],
            ])
            ->add('method', 'choice', [
                'label' => 'form.cashDocumentType.payment_way',
                'required' => true,
                'multiple' => false,
                'empty_value' => '',
                'group' => $options['groupName'],
                'choices' => $options['methods']
            ])
            ->add('method', 'choice', [
                'label' => 'form.cashDocumentType.payment_way',
                'required' => true,
                'multiple' => false,
                'empty_value' => '',
                'group' => $options['groupName'],
                'choices' => $options['methods']
            ])
            ->add('document_date', 'date', [
                'label' => 'form.cashDocumentType.document_date',
                'required' => true,
                'group' => $options['groupName'],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add(
                'isPaid', 'checkbox', [
                'label' => 'form.cashDocumentType.is_paid',
                'required' => false,
                'group' => $options['groupName'],
            ])
            ->add('paid_date', 'date', [
                'label' => 'form.cashDocumentType.paid_date',
                'required' => false,
                'group' => $options['groupName'],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('number', 'text', [
                'label' => 'form.cashDocumentType.number',
                'group' => $options['groupName'],
                //'attr' => ['class' => 'input-sm'],
                'required' => true,
            ])
            ->add('note', 'textarea', [
                'label' => 'form.cashDocumentType.comment',
                'group' => $options['groupName'],
                'required' => false,
            ]);

        $builder->get('organizationPayer')->addModelTransformer(new EntityToIdTransformer($this->documentManager, 'MBHPackageBundle:Organization'));
        $builder->get('touristPayer')->addModelTransformer(new EntityToIdTransformer($this->documentManager, 'MBHPackageBundle:Tourist'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\CashBundle\Document\CashDocument',
            'methods' => [],
            'operations' => [],
            'groupName' => null,
            'payer' => null,
            'payers' => []
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_cashbundle_cashdocumenttype';
    }

}
