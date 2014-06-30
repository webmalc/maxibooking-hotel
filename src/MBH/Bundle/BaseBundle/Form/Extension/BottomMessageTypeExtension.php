<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BottomMessageTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('bottom', $options['bottom']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['bottom'] = $form->getConfig()->getAttribute('bottom');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'bottom' => null,
        ));
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
?>