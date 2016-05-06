<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use MBH\Bundle\HotelBundle\Document\Hotel;


class RecordFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recordDateFrom', 'date', [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'с',
                ],
            ])
            ->add('recordDateTo', 'date', [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'по',
                ],
            ])
			->add('operation', 'choice', [
                'required' => false,
                'choices' => [
                    'in' => 'warehouse.record.in',
                    'out' => 'warehouse.record.out',
                ],
                'attr' => [
					'class' => '',
				],
            ])
            ->add('hotel', 'document', [
                'required' => false,
                'class' => Hotel::class,
            ])
			->add('wareItem', 'document', [
				'required' => false,
				'class' => WareItem::class,
				'group_by' => 'category',
			])
            ->add('search', 'text', [
                'required' => false
            ])
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\WarehouseBundle\Document\RecordFilter',
            'operations' => [],
        ]);
    }

    public function getName()
    {
        return 'mbh_warehousebundle_recordfiltertype';
    }

}
