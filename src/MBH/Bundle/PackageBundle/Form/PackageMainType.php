<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class PackageMainType
 */
class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', 'date', [
                'label' => 'Заезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker begin-datepiker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('end', 'date', [
                'label' => 'Отъезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker end-datepiker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('roomType', 'document', [
                'label' => 'Тип номера',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'Номер',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ])
            ->add('adults', 'choice', [
                'label' => 'Взрослых',
                'group' => 'Номер',
                'required' => true,
                'group' => 'Номер',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children', 'choice', [
                'label' => 'Детей',
                'group' => 'Номер',
                'required' => true,
                'group' => 'Номер',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('isSmoking', 'checkbox', [
                'label' => 'Курящий?',
                'required' => false,
                'group' => 'Номер',
            ]);

        if ($options['price'] && 0) {
            $builder->add('price', 'text', [
                'label' => 'form.packageMainType.price',
                'required' => true,
                'group' => 'Цена',
                'error_bubbling' => true,
                'property_path' => 'packagePrice',
                'attr' => [
                    'class' => 'price-spinner'
                ],
            ]);
        }

        if ($options['promotion']) {
            $package = $options['package'];
            /** @var Package $package */
            if($package && $package->getPromotion() && !in_array($package->getPromotion(), $options['promotions'])) {
                $options['promotions'][] = $package->getPromotion();
            }
            $builder
                ->add('promotion', 'document', [
                    'label' => 'form.packageMainType.promotion',
                    'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                    'required' => false,
                    'group' => 'Акция',
                    'choices' => $options['promotions']
                    /*'query_builder' => function (DocumentRepository $repository) {
                        return $repository->createQueryBuilder()->field('_id')
                    }*/
                ]);
        }

        if($options['discount']) {
            $builder
                ->add('discount', 'text', [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'Скидка'
                ])
                ->add('isPercentDiscount', 'checkbox', [
                    'label' => 'form.packageMainType.isPercentDiscount',
                    'required' => false,
                    'group' => 'Скидка'
                ]);
        }
        $builder
            ->add('note', 'textarea', [
                'label' => 'form.packageMainType.comment',
                'group' => 'Информация',
                'required' => false,
            ]);

        if ($options['corrupted']) {
            $builder
                ->add('corrupted', 'checkbox', [
                    'label' => 'Повреждена?',
                    'required' => false,
                    'group' => 'Информация',
                    'help' => 'Бронь с поврежденной информацией. Подробности в комментарии к брони.'
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'price' => false,
            'discount' => false,
            'hotel' => null,
            'corrupted' => false,
            'promotion' => false,
            'promotions' => [],
            'package' => null
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
