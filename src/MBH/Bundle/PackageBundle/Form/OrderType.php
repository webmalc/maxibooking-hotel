<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

class OrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('source', 'document', [
                    'label' => 'Источник',
                    'required' => false,
                    'multiple' => false,
                    'class' => 'MBHPackageBundle:PackageSource',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder('q')
                            ->field('deletedAt')->equals(null)
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc'])
                            ;
                    },
                ])
                ->add('note', 'textarea', [
                    'label' => 'Комментарий',
                    'required' => false,
                ])
                ->add('confirmed', 'checkbox', [
                    'label' => 'Подтвержден?',
                    'value' => true,
                    'required' => false
                ])
        ;
                
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Order',
        ));
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_order_type';
    }

}
