<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ServiceCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', 'text', [
                    'label' => 'mbhpricebundle.form.servicecategorytype.nazvaniye',
                    'required' => true,
                    'attr' => ['placeholder' => 'mbhpricebundle.form.servicecategorytype.osnovnyyeuslugi']
                ])
                ->add('title', 'text', [
                    'label' => 'mbhpricebundle.form.servicecategorytype.vnutrenneyenazvaniye',
                    'required' => false,
                    'attr' => ['placeholder' => 'Основные услуги - лето ' . date('Y')],
                    'help' => 'Название для использования внутри MaxiBooking'
                ])
                ->add('description', 'textarea', [
                    'label' => 'mbhpricebundle.form.servicecategorytype.opisaniye',
                    'required' => false,
                    'help' => 'mbhpricebundle.form.servicecategorytype.opisaniyekategoriiuslugdlyaonlaynbronirovaniya'
                ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\ServiceCategory'
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_pricebundle_service_category_type';
    }

}
