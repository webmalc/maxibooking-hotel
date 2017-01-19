<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use MBH\Bundle\PriceBundle\Document\Criteria\TariffQueryCriteria;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Services\PromotionConditionFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class TariffFilterType
 * @package MBH\Bundle\PriceBundle\Form
 */
class TariffFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('end', DateType::class, [
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'required' => false
            ])
            ->add('isOnline',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TariffQueryCriteria::ON => 'status.on',
                    TariffQueryCriteria::OFF => 'status.off'
                ]
            ])
            ->add('isEnabled',  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class, [
                'required' => false,
                'choices' => [
                    TariffQueryCriteria::ON => 'state.on',
                    TariffQueryCriteria::OFF => 'state.off'
                ]
            ])
            ->add('search', TextType::class, [
                'required' => false
            ])->getForm();
    }


    public function getBlockPrefix()
    {
        return 'mbh_filter_form';
    }



    public function getDefaultOptions(array $options)
    {
        $options['csrf_protection'] = true;

        return $options;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'MBH\Bundle\PriceBundle\Document\Criteria\TariffQueryCriteria',
            ]);
    }
}