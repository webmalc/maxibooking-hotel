<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldsetExtension extends AbstractTypeExtension
{
    private $rootView;

    public function getExtendedType()
    {
        // расширение будет работать с любым типом полей
        return 'form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            // По умолчанию группировка не происходит.
            'group' => null,
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $group = $options['group'];

        if (null === $group) {
            return;
        }

        $root = $this->getRootView($view);
        $root->vars['groups'][$group][] = $form->getName();
    }

    public function getRootView(FormView $view)
    {
        $root = $view->parent;

        while (null === $root) {
            $root = $root->parent;
        }

        return $root;
    }
}