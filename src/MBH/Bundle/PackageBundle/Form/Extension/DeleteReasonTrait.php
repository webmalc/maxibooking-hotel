<?php namespace MBH\Bundle\PackageBundle\Form\Extension;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

trait DeleteReasonTrait
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('note', TextareaType::class, [
                'label' => 'report.porter.note',
                'required' => false,
                'attr' => ['rows' => '10'],
                'label_attr' => ['class' => 'col-sm-2', 'class' => 'col-md-2']
            ])
            ->add('deleteReason', DocumentType::class, [
                'label' => 'modal.form.delete.reasons.reason',
                'label_attr' => ['class' => 'col-sm-2', 'class' => 'col-md-2'],
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