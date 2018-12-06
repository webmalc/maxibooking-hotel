<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Form\SocialNetworking;


use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSNSs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteSocialNetworkingServicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'snss',
            CollectionType::class,
            [
                'entry_type' => SocialNetworkingServiceType::class,
                'label' => false,
                'group' => 'no-group',
                'entry_options' => [
                    'group' => 'no-group',
                ]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => HolderSNSs::class
            ]
        );
    }
}