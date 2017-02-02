<?php namespace MBH\Bundle\PackageBundle\Form\Extension;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

trait DeleteReasonTrait
{
    public function buildForm($builder, array $options)
    {
        $builder
            ->add('note', TextareaType::class, [
                'label' => 'report.porter.note',
            ])
            ->add('deleteReason', DocumentType::class, [
                'label' => 'modal.form.delete.reasons.reason',
                'class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder()
                        ->field('deletedAt')->exists(false)
                        ->sort(['fullTitle' => 'asc', 'title' => 'asc']);
                },
                'required' => true
            ])
            ->add('order', HiddenType::class, [
                'mapped' => false
            ])
        ;

    }
}