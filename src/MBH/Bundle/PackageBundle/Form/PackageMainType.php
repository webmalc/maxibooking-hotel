<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'begin',
                'date',
                array(
                    'label' => 'Заезд',
                    'group' => 'Заезд/отъезд',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array(
                        'class' => 'datepicker begin-datepiker input-small',
                        'data-date-format' => 'dd.mm.yyyy'
                    ),
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'label' => 'Отъезд',
                    'group' => 'Заезд/отъезд',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'required' => true,
                    'error_bubbling' => true,
                    'attr' => array(
                        'class' => 'datepicker end-datepiker input-small',
                        'data-date-format' => 'dd.mm.yyyy'
                    ),
                )
            )
            ->add(
                'roomType',
                'document',
                [
                    'label' => 'Тип номера',
                    'class' => 'MBHHotelBundle:RoomType',
                    'group' => 'Номер',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder('q')
                            ->field('hotel.id')->equals($options['hotel']->getId())
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                    },
                    'required' => true
                ]
            )
            ->add(
                'adults',
                'choice',
                [
                    'label' => 'Взрослых',
                    'group' => 'Номер',
                    'required' => true,
                    'group' => 'Номер',
                    'multiple' => false,
                    'choices' => range(0, 10),
                    'attr' => array('class' => 'input-xxs plain-html'),
                ]
            )
            ->add(
                'children',
                'choice',
                [
                    'label' => 'Детей',
                    'group' => 'Номер',
                    'required' => true,
                    'group' => 'Номер',
                    'multiple' => false,
                    'choices' => range(0, 10),
                    'attr' => array('class' => 'input-xxs plain-html'),
                ]
            )
            ->add(
                'isSmoking',
                'checkbox',
                [
                    'label' => 'Курящий?',
                    'required' => false,
                    'group' => 'Номер',
                ]
            );

        if ($options['price']) {
            $builder->add(
                'price',
                'text',
                [
                    'label' => 'form.packageMainType.price',
                    'required' => true,
                    'group' => 'Цена',
                    'error_bubbling' => true,
                    'property_path' => 'packagePrice',
                    'attr' => [
                        'class' => 'price-spinner'
                    ],
                ]
            );
        }

        $builder
            ->add(
                'discount',
                'text',
                [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'Цена',
                    'attr' => [
                        'class' => 'discount-spinner'
                    ],
                ]
            )
            ->add(
                'note',
                'textarea',
                [
                    'label' => 'form.packageMainType.comment',
                    'group' => 'Информация',
                    'required' => false,
                ]
            );

        if ($options['corrupted']) {
            $builder
                ->add(
                    'corrupted',
                    'checkbox',
                    [
                        'label' => 'Повреждена?',
                        'required' => false,
                        'group' => 'Информация',
                        'help' => 'Бронь с поврежденной информацией. Подробности в комментарии к брони.'
                    ]
                );
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
                'price' => false,
                'hotel' => null,
                'corrupted' => false
            )
        );
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
