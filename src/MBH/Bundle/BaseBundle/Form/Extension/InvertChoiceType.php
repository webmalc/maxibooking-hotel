<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvertChoiceType extends ChoiceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (is_array($options['choices']) && !empty($options['choices'])) {

            if(is_array(array_values($options['choices'])[0])) {
                array_walk($options['choices'], function (&$item){
                    dump($item);
                    $item = array_flip($item);
                });
            } else {
                $options['choices'] = array_flip($options['choices']);
            }
        }
        parent::buildForm($builder, $options);
    }
}