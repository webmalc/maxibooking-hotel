<?php


namespace MBH\Bundle\SearchBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\SearchBundle\Document\SearchConditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchConditionsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'begin',
                DateType::class,
                [
                    'format' => 'dd.MM.yyyy',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'end',
                DateType::class,
                [
                    'format' => 'dd.MM.yyyy',
                    'widget' => 'single_text',
                ]
            )
            ->add('adults', NumberType::class)
            ->add('children', NumberType::class)
            ->add(
                'roomTypes',
                DocumentType::class,
                [
                    'class' => RoomType::class,
                    'required' => false,
                    'multiple' => true,
                    'choice_label' => 'id'
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'data_class' => SearchConditions::class,
                    'csrf_protection' => false,
                ]
            );
    }

}