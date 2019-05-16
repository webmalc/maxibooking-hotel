<?php
/**
 * Date: 16.05.19
 */

namespace MBH\Bundle\OnlineBundle\Form\MBSite;


use MBH\Bundle\OnlineBundle\Document\SiteContent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUseBannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'useBanner',
                CheckboxType::class,
                [
                    'group'    => 'no-group',
                    'label'    => false,
                    'required' => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => SiteContent::class,
            ]
        );
    }
}
