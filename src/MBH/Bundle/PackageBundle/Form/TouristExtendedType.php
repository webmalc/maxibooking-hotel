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
                'label' => 'form.TouristExtendedType.citizenship',
                'group' => 'form.TouristExtendedType.group.person',
                'empty_value' => ''
            ])
            /*->add('birthplace', 'mbh_birthplace', [
                'required' => false,
                'label' => 'form.TouristExtendedType.birthplace',
                'data_class' => 'MBH\Bundle\PackageBundle\Document\BirthPlace',
                'group' => 'form.TouristExtendedType.group.person'
            ])*/
            /*->add('documentRelation', 'mbh_document_relation', [
                'label' => 'form.TouristExtendedType.documentRelation',
                'data_class' => 'MBH\Bundle\PackageBundle\Document\DocumentRelation',
                'group' => 'form.TouristExtendedType.group.documentRelation',//'DocumentRelation',
            ])*/
            /*->add('address_object_decomposed', 'mbh_address_object_decomposed', [
                'label' => 'form.TouristExtendedType.address_object_decomposed',
                'required' => false,
                'group' => 'form.TouristExtendedType.group.liveAddress',
                'data_class' => 'MBH\Bundle\PackageBundle\Document\AddressObjectDecomposed'
            ])*/
            ->add('address_object_combined', 'text', [
                'label' => 'form.TouristExtendedType.address_object_combined',
                'group' => 'form.TouristExtendedType.group.person',//'form.TouristExtendedType.group.liveAddress',
                'required' => false,
            ])
            ->add('address_object', 'text', [
                'label' => ' ',//'form.TouristExtendedType.address_object',
                'group' => 'form.TouristExtendedType.group.person',//'form.TouristExtendedType.group.liveAddress',
                'required' => false,
                'help' => 'Передача адреса в виде кода (AOID) Федеральной информационной адресной системы (ФИАС)'
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