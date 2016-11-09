<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvertChoiceType extends ChoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (is_array($options['choices'])) {
            $options['choices'] = array_flip($options['choices']);
        }
        parent::buildForm($builder, $options);
    }
}