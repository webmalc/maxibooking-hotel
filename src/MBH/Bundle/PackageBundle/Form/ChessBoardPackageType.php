<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\ODM\MongoDB\Types\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChessBoardPackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'label' => 'form.chessBoard.begin.label',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'error_bubbling' => true,
                'attr' => [
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                ]
            ])
            ->add('end', DateType::class, [
                'label' => 'form.chessBoard.end.label',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => [
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                ]
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'form.chessBoard.roomType.label',
                'class' => 'MBHHotelBundle:RoomType',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true,
                'attr' => [
                ]
            ])
            ->add('accommodation', DocumentType::class, [
                'label' => 'form.chessBoard.accommodation.label',
                'required' => false,
                'multiple' => false,
                'class' => 'MBHHotelBundle:Room',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->field('roomType.id')->equals($options['roomTypeId'])
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'attr' => [
                ]
            ])
            ->add('adults', ChoiceType::class, [
                'label' => 'form.chessBoard.adults.label',
                'required' => true,
                'multiple' => false,
                'choices' => range(0, 10),
                'empty_data' => '1',
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children', ChoiceType::class, [
                'label' => 'form.chessBoard.children.label',
                'required' => true,
                'empty_data' => '0',
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])

            ->add('tariff', ChoiceType::class, [
                'label' => 'form.chessBoard.tariff.label',
                'required' => true,
                'multiple' => false,
                'choices' => $options['tariffChoices']

            ])
            ->add('price', TextType::class, [
                'label' => 'form.chessBoard.price.label',
                'required' => true,
                'empty_data' => '0',
                'attr' => [
                    'class' => 'price-spinner'
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'hotel' => null,
            'package' => null,
            'roomTypeId' => null,
            'tariffChoices' => []
        ]);
    }

    public function getName()
    {
        return '';
    }
}
