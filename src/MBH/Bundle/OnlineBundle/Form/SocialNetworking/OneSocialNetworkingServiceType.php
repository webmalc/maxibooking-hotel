<?php
/**
 * Created by PhpStorm.
 * Date: 22.11.18
 */

namespace MBH\Bundle\OnlineBundle\Form\SocialNetworking;


use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSocialLinks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TODO: rename
 */
class OneSocialNetworkingServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'socialServices',
            CollectionType::class,
            [
                'entry_type'    => SocialNetworkingServiceType::class,
                'label'         => false,
                'group'         => 'no-group',
                'entry_options' => [
                    'group' => 'no-group',
                ],
            ]
        );

        $builder->add(
            'aggregatorServices',
            CollectionType::class,
            [
                'entry_type'    => SocialNetworkingServiceType::class,
                'label'         => false,
                'group'         => 'no-group',
                'entry_options' => [
                    'group' => 'no-group',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => HolderSocialLinks::class,
            ]
        );
    }
}
