<?php
/**
 * Created by PhpStorm.
 * Date: 15.10.18
 */

namespace MBH\Bundle\PriceBundle\Form;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\PriceBundle\Document\TariffCombinationHolder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use MBH\Bundle\PriceBundle\Document\TariffRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TariffCombinationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'parentId',
                HiddenType::class,
                [
                    'empty_data' => $options['parent_tariff']->getId(),
                ]
            );

        $builder
            ->add(
                'position',
                HiddenType::class,
                [
                    'attr' => [
                        'readonly' => true,
                        'class'    => 'tariff-position',
                    ],
                ]
            )
            ->add(
                'combinationTariffId',
                InvertChoiceType::class,
                [
                    'choices'     => $options['tariffs_for_select'],
                    'placeholder' => 'mbhpricebundle.form.tariff_combination_holder.choose',
                    'label'       => false,
                    'attr'        => [
                        'class' => 'plain-html',
                    ],
                    'group'       => 'no-group',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('tariffs_for_select');
        $resolver->setDefined('parent_tariff');

        $resolver->setDefaults([
            'data_class' => TariffCombinationHolder::class,
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['embedded'] = true;
    }
}