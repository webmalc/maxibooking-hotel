<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\HotelBundle\Document\Hotel;


class RecordType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('wareItem', 'document', [
				'required' => true,
				'class' => WareItem::class,
				'label' => 'warehouse.items.title',
				'group_by' => 'category',
			])
            ->add('qtty', 'text', [
                'label' => 'warehouse.record.quantity',
                'required' => true,
                'attr' => ['class' => 'spinner spinner-0f',],
            ])
            ->add('unit', 'text', [
                'label' => 'warehouse.field.unit',
                'mapped' => false,
				'required' => false,
				'disabled' => true,
				'attr' => ['class' => 'input-small',],
            ])
            ->add('price', 'text', [
                'label' => 'warehouse.items.price',
                'required' => true,
                'attr' => ['class' => 'spinner price-spinner',],
            ])
            ->add('amount', 'text', [
                'label' => 'warehouse.record.amount',
				'required' => true,
				'attr' => ['class' => 'spinner price-spinner',],
            ])
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\Record',
//            'operations' => [],
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_warehousebundle_recordtype';
    }

}
