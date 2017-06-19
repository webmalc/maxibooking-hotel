<?php

namespace MBH\Bundle\PriceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\TranslatorInterface;

class ServiceCategoryType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullTitle', TextType::class, [
                    'label' => 'mbhpricebundle.form.servicecategorytype.nazvaniye',
                    'required' => true,
                    'attr' => ['placeholder' => 'mbhpricebundle.form.servicecategorytype.osnovnyyeuslugi']
                ])
                ->add('title', TextType::class, [
                    'label' => 'mbhpricebundle.form.servicecategorytype.vnutrenneyenazvaniye',
                    'required' => false,
                    'attr' => ['placeholder' => $this->translator->trans('mbhpricebundle.form.servicecategorytype.vnutrenneyenazvaniye.placeholder', ['%year%' => date('Y')])],
                    'help' => 'mbhpricebundle.form.servicecategorytype.vnutrenneyenazvaniye.help'
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'mbhpricebundle.form.servicecategorytype.opisaniye',
                    'required' => false,
                    'help' => 'mbhpricebundle.form.servicecategorytype.opisaniyekategoriiuslugdlyaonlaynbronirovaniya'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PriceBundle\Document\ServiceCategory'
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_pricebundle_service_category_type';
    }

}
