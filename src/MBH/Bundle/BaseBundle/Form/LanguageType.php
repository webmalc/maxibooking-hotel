<?php

namespace MBH\Bundle\BaseBundle\Form;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class LanguageType

 */
class LanguageType extends AbstractType
{
    use ContainerAwareTrait;

    public function getParent()
    {
        return  \MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'form.languageType.label',
            'choice_label' => function($label) {
                return 'languages.'.$label;
            },
            //'choices_as_values' => true,
            'choice_list' => new ArrayChoiceList(
                $this->container->getParameter('mbh.languages'),
                function($value) {return $value;}
            )
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_language';
    }
}