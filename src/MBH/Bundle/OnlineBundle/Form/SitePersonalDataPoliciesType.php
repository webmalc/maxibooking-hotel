<?php
/**
 * Created by PhpStorm.
 * Date: 11.09.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class SitePersonalDataPoliciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('personalDataPolicies', TextareaType::class, [
            'label'    => 'site_form.pers_data_policy.label',
            'attr'     => ['class' => 'tinymce'],
            'required' => false,
        ]);
    }
}