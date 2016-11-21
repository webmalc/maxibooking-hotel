<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\BaseBundle\Form\Extension\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;


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
                'attr' => array(
                    'class' => 'datepicker begin-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('end', DateType::class, [
                'label' => 'form.chessBoard.end.label',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => true,
                'attr' => array(
                    'class' => 'datepicker end-datepicker input-small',
                    'data-date-format' => 'dd.mm.yyyy'
                )
            ])
            ->add('roomType', DocumentType::class, [
                'label' => 'form.chessBoard.roomType.label',
                'class' => 'MBHHotelBundle:RoomType',
                'group' => 'Номер',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder('q')
                        ->field('hotel.id')->equals($options['hotel']->getId())
                        ->field('deletedAt')->equals(null)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ])
            ->add('adults', ChoiceType::class, [
                'label' => 'Взрослых',
                'required' => true,
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ->add('children', ChoiceType::class, [
                'label' => 'Детей',
                'required' => true,
                'multiple' => false,
                'choices' => range(0, 10),
                'attr' => array('class' => 'input-xxs plain-html'),
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getName()
    {
        return 'mbhpackage_bundle_chess_board_package_type';
    }
}
