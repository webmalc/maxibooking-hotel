<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PackageCopyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('package', DocumentType::class, [
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
            ->add('tourists', CheckboxType::class, [
                    'label' => 'form.packageCopyType.guests',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_guests_to_selected_reservation'
                ])
            ->add('services', CheckboxType::class, [
                    'label' => 'form.packageCopyType.services',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_all_services_to_selected_reservation'
                ])
            ->add('accommodation', CheckboxType::class, [
                    'label' => 'form.packageCopyType.placement',
                    'group' => 'form.packageCopyType.transfer_parameters',
                    'value' => true,
                    'required' => false,
                    'help' => 'form.packageCopyType.should_we_transfer_placement_to_selected_reservation'
                ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_copy_type';
    }

}
