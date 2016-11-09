<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PackageServiceType
 */
class PackageServiceType extends AbstractType
{
    use ContainerAwareTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['package'] instanceof Package) {
            throw new \Exception('Package required.');
        }
        /** @var Package $package */
        $package = $options['package'];
        $services = $this->container->get('doctrine_mongodb')
            ->getRepository('MBHPriceBundle:Service')->getAvailableServicesForPackage($package);
        $builder
            ->add('service', 'document', [
                'label' => 'form.packageServiceType.service',
                'class' => 'MBHPriceBundle:Service',
                'choices' => $services,
                'empty_value' => '',
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service',
            ])
            ->add('price', 'text', [
                'label' => 'form.packageServiceType.price',
                'required' => true,
                'group' => 'form.packageServiceType.add_service',
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'price-spinner sm']
            ])
            ->add(
                'begin',
                'date',
                array(
                    'label' => 'form.packageServiceType.begin',
                    'group' => 'form.packageServiceType.add_service',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'form.packageServiceType.date_add_begin',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker begin-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ),
                )
            )
            ->add(
                'end',
                'date',
                array(
                    'label' => 'form.packageServiceType.end',
                    'group' => 'form.packageServiceType.add_service',
                    'widget' => 'single_text',
                    'format' => 'dd.MM.yyyy',
                    'help' => 'form.packageServiceType.date_add_end',
                    'required' => false,
                    'attr' => array(
                        'class' => 'datepicker end-datepicker input-small',
                        'data-date-format' => 'dd.mm.yyyy',
                    ),
                )
            )
            ->add('nights', 'text', [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('persons', 'text', [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('time', 'time', [
                'label' => 'form.packageServiceType.time',
                'required' => false,
                'group' => 'form.packageServiceType.add_service',
                'attr' => ['class' => 'sm'],
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('checks', CheckboxType::class, [
                'label' => 'form.packageServiceType.extension_package',
                'required' => false,
                'mapped' => false,
                'group' => 'form.packageServiceType.add_service',
                'attr' => array('checked' => 'checked'),
                'help' => 'form.packageServiceType.extension_package_help',
            ])
            ->add('amount', 'text', [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm'],
                'help' => '-'
            ])
            ->add('note', 'textarea', [
                'label' => 'form.packageServiceType.comment',
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'package' => null,
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageService',
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_packagebundle_package_service_type';
    }

}
