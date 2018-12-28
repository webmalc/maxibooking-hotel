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


class RecordFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recordDateFrom', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'warehouse.resources.form.recordfiltertype.with',
                ],
            ])
            ->add('recordDateTo', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'mbhwarehousebundle.form.recordfiltertype.po',
                ],
            ])
			->add('operation',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    'in' => 'warehouse.record.in',
                    'out' => 'warehouse.record.out',
                ],
                'attr' => [
					'class' => '',
				],
            ])
            ->add('hotel', DocumentType::class, [
                'required' => false,
                'class' => Hotel::class,
            ])
			->add('wareItem', DocumentType::class, [
				'required' => false,
				'class' => WareItem::class,
				'group_by' => 'category',
			])
            ->add('search', TextType::class, [
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

    public function getBlockPrefix()
    {
        return 'mbh_warehousebundle_recordfiltertype';
    }

}
