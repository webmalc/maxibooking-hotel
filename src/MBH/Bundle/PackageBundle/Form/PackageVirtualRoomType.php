<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageVirtualRoomType
 */
class PackageVirtualRoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Package $package */
        $package = $options['package'];
        $isChain = $options['isChain'];

        $builder
            ->add('virtualRoom', DocumentType::class, [
                'label' => 'Номер',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'modal.form.virtual_room.virtual_room.group',
                'query_builder' => function (RoomRepository $dr) use ($package, $isChain) {
                    return $isChain
                        ? $dr->getVirtualRoomsForPackageQB($package, true)
                        : $dr->getVirtualRoomsForPackageQB($package);
                },
                'required' => false
            ])
            ->add('isChainMoved', CheckboxType::class, [
                'label' => 'modal.form.virtual_room.is_chain_moved.label',
                'group' => 'modal.form.virtual_room.virtual_room.group',
                'mapped' => false,
                'required' => false,
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'package' => null,
            'isChain' => null
         ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_virtual_room_type';
    }

}
