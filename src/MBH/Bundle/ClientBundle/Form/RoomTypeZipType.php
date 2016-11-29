<?php

namespace MBH\Bundle\ClientBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Bundle\MongoDBBundle\Tests\Fixtures\Form\Document;
use Doctrine\Tests\Common\Annotations\Ticket\Doctrine\ORM\Mapping\Entity;
use MBH\Bundle\BaseBundle\Form\Extension\TimeType;
use MBH\Bundle\ClientBundle\Document\RoomTypeZip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomTypeZipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('categories', DocumentType::class, [
                'label' => 'Выберите категории',
                'class' => 'MBH\Bundle\HotelBundle\Document\RoomTypeCategory',
                'multiple' => true,
                'required' => false,
                'group' => 'form.RoomTypeZip.group',
                'help' => 'Выберите категории для обновления'
            ])
            ->add('time', ChoiceType::class , [
                'label' => 'Выберите время',
                'group' => 'form.RoomTypeZip.group',
                'multiple' => true,
                'choices' => RoomTypeZip::getTimes(),
                'help' => 'Выберите время в которое будет происходить обновление'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\ClientBundle\Document\RoomTypeZip',
            'hours' => null,
        ]);
    }

    public function getName()
    {
        return 'mbh_bundle_clientbundle_room_type_zip';
    }

}
