<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldsetExtension extends AbstractTypeExtension
{
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * Add the image_path option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('hasGroup')
            ->setDefined('group')
            ->setDefined('flow_step')
            ->setDefaults(['group' => null, 'hasGroups' => true, 'flow_step' => null]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $group = $options['group'];
        $view->vars['hasGroups'] = $options['hasGroups'];

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