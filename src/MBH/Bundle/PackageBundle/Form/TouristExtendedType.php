<?php

namespace MBH\Bundle\PackageBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TouristExtendedType
 * @package MBH\Bundle\PackageBundle\Form
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TouristExtendedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //Person
            ->add('citizenship', 'document', [
                'class' => 'MBH\Bundle\VegaBundle\Document\VegaState',
                'group' => 'Person'
            ])
            ->add('birthplace', 'mbh_birthplace', [
                'required' => false,
                'data_class' => 'MBH\Bundle\PackageBundle\Document\BirthPlace',
                'group' => 'Person'
            ])
            /* TODO
            ->add('last_name_lat', 'text', [
                'mapped' => false
            ]);
            ->add('first_name_lat', 'text', [
                'mapped' => false
            ]);
            ->add('middle_name_lat', 'text', [
                'mapped' => false
            ]);*/
            ->add('documentRelation', 'mbh_document_relation', [
                'data_class' => 'MBH\Bundle\PackageBundle\Document\DocumentRelation',
                'group' => 'DocumentRelation',//'DocumentRelation',
            ])
            //LiveAddress
            ->add('address_object', 'text', [
                'group' => 'LiveAddress',
                'required' => false,
                'help' => 'Передача адреса в виде кода (AOID) Федеральной информационной адресной системы (ФИАС)'
            ])
            ->add('address_object_decomposed', 'mbh_address_object_decomposed', [
                'required' => false,
                'group' => 'LiveAddress',
                'data_class' => 'MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed'
            ])
            ->add('address_object_combined', 'text', [
                'group' => 'LiveAddress',
                'required' => false,
            ]);
            //todo ->add('reg_address', 'text', ['mapped' => false,'required' => false,
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'mbh_tourist_extended';
    }
}