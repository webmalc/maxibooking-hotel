<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class InvoiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('operation',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'warehouse.record.operation',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['operations'],
            ])
            ->add('docNumber', TextType::class, [
                'label' => 'Document Number',
                'required' => true,
            ])
            ->add('invoiceDate', DateType::class, [
                'label' => 'Invoice Date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
                ],
            ])
            ->add('organization', DocumentType::class, [
                'label' => 'Organization',
                'required' => true,
                'class' => Organization::class,
            ])
            ->add('hotel', DocumentType::class, [
                'label' => 'form.hotelType.placeholder_hotel',
                'required' => false,
                'class' => Hotel::class,
            ])
			->add('records', CollectionType::class, [
				'entry_type' => RecordType::class,
				'by_reference' => false,
				'allow_add' => true,
				'allow_delete' => true,
                'prototype' => true,
			]);
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\Invoice',
            'operations' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_warehousebundle_invoicetype';
    }

}
