<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderSearchType extends AbstractType
{
    const PREFIX = 'mbh_bundle_onlinebundle_order_search_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userNameVisible = false;

        if (!empty($options['data']) && $options['data']->isUserNameVisible()) {
            $userNameVisible = true;
        }

        $commonInputAttr = ['class' => 'form-control input-sm'];
        $commonLabelAttr = ['class' => 'col-form-label col-form-label-sm'];
        $commonGroup = 'form.online.order_search';

        $builder
            ->add(
                'numberOrder',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.numberOrder',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            )
            ->add(
                'phoneOrEmail',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.phoneOrEmail',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            )
            ->add(
                'configId',
                HiddenType::class
            );
        if ($userNameVisible) {
            $builder->add(
                'userName',
                TextType::class,
                [
                    'label'      => 'form.online.order_search.userName',
                    'label_attr' => $commonLabelAttr,
                    'group'      => $commonGroup,
                    'attr'       => $commonInputAttr,
                ]
            );
        }

        $builder
            ->add(
                'findOrder',
                SubmitType::class,
                [
                    'label' => 'form.online.order_search.button_search',
                    'attr'  => ['class' => 'btn btn-primary btn-block'],
                ]
            );
    }

    public function getBlockPrefix()
    {
        return self::PREFIX;
    }
}