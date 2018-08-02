<?php

namespace MBH\Bundle\BaseBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HelpMessageTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('help', $options['help']);
        $builder->setAttribute('addon', $options['addon']);
        $builder->setAttribute('addonText', $options['addonText']);
        $builder->setAttribute('preAddonText', $options['preAddonText']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $form->getConfig()->getAttribute('help');
        $view->vars['addon'] = $form->getConfig()->getAttribute('addon');
        $view->vars['addonText'] = $form->getConfig()->getAttribute('addonText');
        $view->vars['preAddonText'] = $form->getConfig()->getAttribute('preAddonText');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined(['help', 'addon', 'addonText', 'preAddonText'])
            ->setDefaults(['help' => null, 'addon' => null, 'addonText' => null, 'preAddonText' => null]);
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}