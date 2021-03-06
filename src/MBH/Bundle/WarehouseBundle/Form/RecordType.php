<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RecordType extends AbstractType
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
            ->add('recordDate', DateType::class, [
                'label' => 'warehouse.record.recordDate',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
                ]
            ])
            ->add('hotel', DocumentType::class, [
                'label' => 'form.hotelType.placeholder_hotel',
                'required' => false,
                'class' => Hotel::class,
            ])
			->add('wareItem', DocumentType::class, [
				'required' => false,
				'class' => WareItem::class,
				'label' => 'warehouse.items.title',
				'group_by' => 'category',
			])
            ->add('qtty', TextType::class, [
                'label' => 'warehouse.record.quantity',
                'required' => true,
                'attr' => ['class' => 'spinner price-spinner'],
            ])
            ->add('unit', TextType::class, [
                'label' => 'warehouse.field.unit',
                'mapped' => false,
				'required' => false,
				'disabled' => true,
				'attr' => ['class' => 'input-small'],
            ])
            ->add('price', TextType::class, [
                'label' => 'warehouse.items.price',
                'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
            ])
            ->add('amount', TextType::class, [
                'label' => 'warehouse.record.amount',
				'required' => false,
                'attr' => ['class' => 'spinner price-spinner'],
            ])
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\Record',
            'operations' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_warehousebundle_recordtype';
    }

}
