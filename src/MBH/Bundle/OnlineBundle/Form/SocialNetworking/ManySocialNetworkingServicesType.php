<?php
/**
 * Date: 15.05.19
 */

namespace MBH\Bundle\OnlineBundle\Form\SocialNetworking;


use MBH\Bundle\OnlineBundle\Lib\SocialNetworking\HolderSocialLinks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TODO: rename
 */
class ManySocialNetworkingServicesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'many',
                CollectionType::class,
                [
                    'entry_type' => OneSocialNetworkingServiceType::class,
                    'label' => false,
                    'group' => 'no-group',
                    'mapped' => false,
                    'data' => $builder->getData(),
                    'entry_options' => [
                        'group' => 'no-group',
                        'label' => false
                    ]
                ]
            );
    }
}
