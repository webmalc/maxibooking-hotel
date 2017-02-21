<?php

namespace MBH\Bundle\WarehouseBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\WarehouseBundle\Document\WareItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MBH\Bundle\BaseBundle\Lib\Exception;


class RecordFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $wareCategories = $options['wareCategories'];

        $builder
            ->add('recordDateFrom', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'с',
                ],
            ])
            ->add('recordDateTo', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy',
					'placeholder' => 'по',
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
			->add('wareItem', ChoiceType::class, [
				'required' => false,
                'choices' => $this->getWareItemChoices($options['wareCategories']),
			])
            ->add('search', TextType::class, [
                'required' => false
            ])
		;
    }

    private function getWareItemChoices($wareCategories)
    {
        $ware = [];

        foreach ($wareCategories as $wareCategory) {

            $ware[$wareCategory->getName()]['form.searchType.all_products'] = 'allproducts_' . $wareCategory->getId();

            foreach ($wareCategory->getItems() as $item) {
                $ware[$wareCategory->getName()][$item->getFullTitle()] = $item->getId();
            }

        }

        return $ware;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'wareCategories' => [],
            'dm' => null,
            'method' => 'POST',
            'data_class' => 'MBH\Bundle\WarehouseBundle\Lib\RecordQuery',
            'operations' => [],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_warehousebundle_recordfiltertype';
    }

}
