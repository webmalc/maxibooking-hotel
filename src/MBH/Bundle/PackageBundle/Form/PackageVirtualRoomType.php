<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
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

        if ($options['isChain']) {
            $builder->add('virtualRoomChain', ChoiceType::class, [
                'mapped' => false,
                'group' => 'modal.form.virtual_room.virtual_room.group',
                'choices' => [1,2,3,4]
            ]);
        } else {
            $builder->add('virtualRoom', DocumentType::class, [
                'label' => 'Номер',
                'class' => 'MBHHotelBundle:Room',
                'group' => 'modal.form.virtual_room.virtual_room.group',
                'query_builder' => function (DocumentRepository $dr) use ($package) {
                    return $dr->getVirtualRoomsForPackageQB($package);
                },
                'required' => false
            ]);
        }
        $builder
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
