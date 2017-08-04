<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
        $services = $options['dm']
            ->getRepository('MBHPriceBundle:Service')->getAvailableServicesForPackage($package);

        $builder
            ->add('service', DocumentType::class, [
                'label' => 'form.packageServiceType.service',
                'class' => 'MBHPriceBundle:Service',
                'choices' => $services,
                'placeholder' => '',
                'group' => 'form.packageServiceType.add_service',
                'help' => 'form.packageServiceType.reservation_add_service',
            ])
            ->add('price', TextType::class, [
                'label' => 'form.packageServiceType.price',
                'required' => true,
                'group' => 'form.packageServiceType.add_service',
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'price-spinner sm']
            ])
            ->add('nights', TextType::class, [
                'label' => 'form.packageServiceType.nights_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('persons', TextType::class, [
                'label' => 'form.packageServiceType.guests_amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm']
            ])
            ->add('begin', DateType::class, [
                'label' => 'mbhpackagebundle.form.packageservicetype.data',
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ])
            ->add('end', DateType::class, [
                'label' => 'form.packageServiceType.end',
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'attr' => array('class' => 'datepicker sm', 'data-date-format' => 'dd.mm.yyyy'),
            ])
            ->add('time', TimeType::class, [
                'label' => 'form.packageServiceType.time',
                'required' => false,
                'group' => 'form.packageServiceType.add_service',
                'attr' => ['style' => 'width: 60px'],
                'widget' => 'single_text',
                'html5' => false
            ])
            ->add('amount', TextType::class, [
                'label' => 'form.packageServiceType.amount',
                'required' => true,
                //'data' => 1,
                'group' => 'form.packageServiceType.add_service',
                'error_bubbling' => true,
                'constraints' => new NotBlank(),
                'attr' => ['class' => 'spinner sm'],
                'help' => '-'
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.packageServiceType.comment',
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
            ])
            ->add('recalcWithPackage', CheckboxType::class, [
                'label' => 'Смещаемая?',
                'value' => true,
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
                'help' => 'Смещать ли даты услуги при изменении дат брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('includeArrival', CheckboxType::class, [
                'label' => 'Учитывать заезд?',
                'value' => true,
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
                'help' => 'Учитывать ли дату заезда брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ->add('includeDeparture', CheckboxType::class, [
                'label' => 'Учитывать выезд?',
                'value' => true,
                'group' => 'form.packageServiceType.add_service',
                'required' => false,
                'help' => 'Учитывать ли дату выезда брони?',
                'attr' => ['class' => 'toggle-date'],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'package' => null,
            'dm' => null,
            'data_class' => 'MBH\Bundle\PackageBundle\Document\PackageService',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_service_type';
    }
}
