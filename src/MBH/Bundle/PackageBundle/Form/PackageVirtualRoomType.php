<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\HotelBundle\Document\RoomRepository;
use MBH\Bundle\PackageBundle\Document\Package;
use Symfony\Component\Form\AbstractType;
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

        $builder
            ->add('virtualRoom', DocumentType::class, [
                'label' => 'form.packagevirtualroomtype.nomer',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'form.packagevirtualroomtype.virt_room.group',
                'query_builder' => function (RoomRepository $dr) use ($package) {
                    return $dr->getVirtualRoomsForPackageQB($package);
                },
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Package',
            'package' => null,
         ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_virtual_room_type';
    }
}
