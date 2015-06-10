<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;

class PackageCopyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('package', 'document', [
                    'label' => 'form.packageCopyType.reservation',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'class' => 'MBHPackageBundle:Package',
                    'required' => true,
                    'property' => 'numberWithPayer',
                    'query_builder' => function(DocumentRepository $er) {
                        return $er->createQueryBuilder('q')
                            ->field('end')->gte(new \DateTime('midnight'))
                            ->sort('createdAt', 'desc');
                    },
                    'empty_value' => '',
                    'help' => 'form.packageCopyType.data_transfer_reservation',
                    'constraints' => [
                        new NotBlank(['message' => 'form.packageCopyType.no_data_transfer_reservation_selected'])
                    ]
                ])
            ->add('tourists', 'checkbox', [
                    'label' => 'form.packageCopyType.guests',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_guests_to_selected_reservation'
                ])
            ->add('services', 'checkbox', [
                    'label' => 'form.packageCopyType.services',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_all_services_to_selected_reservation'
                ])
            ->add('accommodation', 'checkbox', [
                    'label' => 'form.packageCopyType.placement',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_placement_to_selected_reservation'
                ])
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_copy_type';
    }

}
