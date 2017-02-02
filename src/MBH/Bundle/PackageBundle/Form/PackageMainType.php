<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageMainType
 */
class PackageMainType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Package $package */
        $package = $options['package'];

        $builder
            ->add('begin', DateType::class, [
                'label' => 'Заезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('end', DateType::class, [
                'label' => 'Отъезд',
                'group' => 'Заезд/отъезд',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('isForceBooking', CheckboxType::class, [
                'label' => 'Принудительное бронирование?',
                'required' => false,
                'group' => 'Заезд/отъезд',
                'help' => 'Игнорировать условия и ограничения при поиске доступного номера?'
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'Тип номера',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'Номер',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ]);
            if ($options['virtualRooms']) {
                $builder
                    ->add('virtualRoom', DocumentType::class, [
                        'label' => 'Виртуальный номер',
                        'class' => 'MBHHotelBundle:Room',
                        'group' => 'Номер',
                        'query_builder' => function (DocumentRepository $dr) use ($package) {
                            return $dr->getVirtualRoomsForPackageQB($package);
                        },
                        'required' => false
                    ]);
            }
            $builder
            ->add('adults',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'Взрослых',
                'group' => 'Номер',
                'required' => true,
                'group' => 'Номер',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'label' => 'Детей',
                'group' => 'Номер',
                'required' => true,
                'group' => 'Номер',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('isSmoking', CheckboxType::class, [
                'label' => 'Курящий?',
                'required' => false,
                'group' => 'Номер',
            ]);

        if ($options['promotion']) {
            if($package && $package->getPromotion() && !in_array($package->getPromotion(), $options['promotions'])) {
                $options['promotions'][] = $package->getPromotion();
            }
            if (count($options['promotions'])) {
                $builder
                    ->add('promotion', DocumentType::class, [
                        'label' => 'form.packageMainType.promotion',
                        'class' => 'MBH\Bundle\PriceBundle\Document\Promotion',
                        'required' => false,
                        'group' => 'Акция',
                        'choices' => $options['promotions']
                    ]);
            }
        }
        if (!$package->getTotalOverwrite() && $options['price']) {
            $builder->add('price', TextType::class, [
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

        if($options['discount']) {
            $builder
                ->add('discount', TextType::class, [
                    'label' => 'form.packageMainType.discount',
                    'required' => false,
                    'group' => 'Скидка'
                ])
                ->add('isPercentDiscount', CheckboxType::class, [
                    'label' => 'form.packageMainType.isPercentDiscount',
                    'required' => false,
                    'group' => 'Скидка'
                ]);
        }
        $builder
            ->add('numberWithPrefix', TextType::class, [
                'label' => 'Номер брони',
                'group' => 'Информация',
                'required' => true,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.packageMainType.comment',
                'group' => 'Информация',
                'required' => false,
            ]);
        if ($package->isDeleted()) {
            $builder
                ->add('deleteReason', DocumentType::class, [
                    'label' => 'modal.form.delete.reasons.reason',
                    'group' => 'modal.form.delete.delete_reason_package',
                    'class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder()
                            ->field('deletedAt')->exists(false)
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                    },
                    'required' => true
                ]);
        }
        if ($options['corrupted']) {
            $builder
                ->add('corrupted', CheckboxType::class, [
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
            'discount' => false,
            'hotel' => null,
            'corrupted' => false,
            'promotion' => false,
            'promotions' => [],
            'package' => null,
            'price' => false,
            'virtualRooms'=> false
         ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_main_type';
    }

}
