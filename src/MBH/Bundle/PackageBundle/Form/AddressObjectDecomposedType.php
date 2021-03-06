<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AddressObjectDecomposedType
 */
class AddressObjectDecomposedType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $regionHelp = $this->translator->trans('form.AddressObjectDecomposedType.region_id.help',
            ['%plusButtonHtml%' => '<a class="add-billing-entity-button" data-entity-type="regions"><i class="fa fa-plus"></i></a>']);

        $builder
            ->add('countryTld', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.country',
                'required' => false,
                'attr' => [
                    'class' => 'billing-text-select billing-country',
                    'data-endpoint-name' => 'countries'
                ],
            ])
            ->add('regionId', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.region',
                'required' => false,
                'attr' => [
                    'class' => 'billing-text-select billing-region',
                    'data-endpoint-name' => 'regions'
                ],
                'help' => $regionHelp
            ])
            ->add('city', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.city',
                'required' => false,
            ])
            ->add('settlement', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.settlement',
                'required' => false,
            ])
            ->add('district', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.district',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.street',
                'required' => false,
            ])
            ->add('house', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.house',
                'required' => false,
            ])
            ->add('corpus', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.corpus',
                'required' => false,
            ])
            ->add('structure', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'spinner',
                ],
                'label' => 'form.AddressObjectDecomposedType.structure.label'
            ])
            ->add('flat', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.flat',
                'required' => false,
            ])
            ->add('zip_code', TextType::class, [
                'label' => 'form.AddressObjectDecomposedType.zip_code',
                'required' => false,
            ])
            ->add('address_object', TextType::class, [
                'label' => 'form.TouristExtendedType.address_object',
                'required' => false,
                'help' => 'form.AddressObjectDecomposedType.address_object.help'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'mbh_address_object_decomposed';
    }

}