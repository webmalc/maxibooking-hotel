<?php


namespace MBH\Bundle\SearchBundle\Form;


use MBH\Bundle\SearchBundle\Document\SearchConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'positivePackageLengthDelta',
                IntegerType::class,
                [
                    'label' => '+&#916; длины искомой брони.',
                    'help' => 'Значение в днях дельты длины брони в сторону увеличения.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'negativePackageLengthDelta',
                IntegerType::class,
                [
                    'label' => '-&#916; длины искомой брони.',
                    'help' => 'Значение в днях дельты длины брони в сторону уменьшения.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'maxAdditionalPackageLength',
                IntegerType::class,
                [
                    'label' => 'Максимум длины брони',
                    'help' => 'Значение в днях максимальной длины брони с учетом дельты.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'minAdditionalPackageLength',
                IntegerType::class,
                [
                    'label' => 'Максимум длины брони',
                    'help' => 'Значение в днях минимальной длины брони с учетом дельты.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'roomTypeResultsShowAmount',
                IntegerType::class,
                [
                    'label' => 'Количество результатов',
                    'help' => 'Количество выводимых результатов гибких дат на каждый тип комнаты.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'mustShowNecessarilyDate',
                CheckboxType::class,
                [
                    'label' => 'Обязательный показ точной даты',
                    'help' => 'Обязательно ли показывать точную дату, даже если данные уже были выведены.',
                    'group' => 'Общая информация',
                    'empty_data' => 'false'
                ]
            )
            ->add(
                'positiveMaxAdditionalSearchDaysAmount',
                IntegerType::class,
                [
                    'label' => 'Принудительное ограничения "вперед"',
                    'help' => 'Ограничение поиска в днях по доп датам в сторону увеличения даты.',
                    'group' => 'Общая информация'
                ]
            )
            ->add(
                'negativeMaxAdditionalSearchDaysAmount',
                IntegerType::class,
                [
                    'label' => 'Принудительное ограничения "назад"',
                    'help' => 'Ограничение поиска в днях по доп датам в сторону уменьшения даты.',
                    'group' => 'Общая информация'
                ]
                )
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'search_config';
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchConfig::class
        ]);
    }

}