<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\HotelBundle\Document\Hotel;
use \MBH\Bundle\PackageBundle\Document\Organization;


class InvoiceType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('operation', 'choice', [
                'label' => 'warehouse.record.operation',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices' => $options['operations'],
            ])
            ->add('docNumber', 'text', [
                'label' => 'Document Number',
                'required' => true,
            ])
            ->add('invoiceDate', 'date', [
                'label' => 'Invoice Date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
                ],
            ])
            ->add('organization', 'document', [
                'label' => 'Organization',
                'required' => true,
                'class' => Organization::class,
            ])
            ->add('hotel', 'document', [
                'label' => 'form.hotelType.placeholder_hotel',
                'required' => false,
                'class' => Hotel::class,
            ])
			->add('records', CollectionType::class, [
				'entry_type' => RecordType::class,
				'by_reference' => false,
				'allow_add' => true,
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

    public function getName()
    {
        return 'mbh_bundle_warehousebundle_invoicetype';
    }

}
