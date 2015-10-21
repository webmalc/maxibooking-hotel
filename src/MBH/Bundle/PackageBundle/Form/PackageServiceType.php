<?php

namespace MBH\Bundle\PackageBundle\Form;

use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
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
            ->add('begin', 'date', [
                'label' => 'Дата',
                'group' => 'form.packageServiceType.add_service',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ])
            ->add('time', 'time', [
                'label' => 'form.packageServiceType.time',
                'required' => false,
                'group' => 'form.packageServiceType.add_service',
                'attr' => ['class' => 'sm'],
                'widget' => 'single_text',
                'html5' => false
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
