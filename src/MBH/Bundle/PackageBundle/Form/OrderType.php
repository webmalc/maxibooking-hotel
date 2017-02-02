<?php

namespace MBH\Bundle\PackageBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Package $package */
        $order = $options['data'];
        $f = $order->isDeleted();
$a = 0;
        $builder
                ->add('source', DocumentType::class, [
                    'label' => 'form.orderType.source',
                    'required' => false,
                    'multiple' => false,
                    'class' => 'MBHPackageBundle:PackageSource',
                    'query_builder' => function(DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder('q')
                            ->field('deletedAt')->equals(null)
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc'])
                            ;
                    },
                ])
                ->add('note', TextareaType::class, [
                    'label' => 'form.orderType.comment',
                    'required' => false,
                ])
                ->add('confirmed', CheckboxType::class, [
                    'label' => 'form.orderType.is_confirmed',
                    'value' => true,
                    'required' => false
                ]);
        if ($order->isDeleted()) {
            $builder
                ->add('deleteReason', DocumentType::class, [
                    'label' => 'modal.form.delete.reasons.reason',
                    'class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
                    'query_builder' => function (DocumentRepository $dr) use ($options) {
                        return $dr->createQueryBuilder()
                            ->field('deletedAt')->exists(false)
                            ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                    },
                    'required' => true
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MBH\Bundle\PackageBundle\Document\Order',
        ));
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_packagebundle_package_order_type';
    }

}
