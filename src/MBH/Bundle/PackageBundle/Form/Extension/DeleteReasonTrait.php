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
            ->add('deleteReason', DocumentType::class, [
                'label' => 'modal.form.delete.reasons.reason',
                'class' => 'MBH\Bundle\PackageBundle\Document\DeleteReason',
                'query_builder' => function (DocumentRepository $dr) use ($options) {
                    return $dr->createQueryBuilder()
                        ->field('deletedAt')->exists(false)
                        ->sort(['isDefault' => 'desc']);
                },
                'required' => true,
                'group' => 'no-group'
            ])
            ->add('note', TextareaType::class, [
                'label' => 'report.porter.note',
                'required' => false,
                'attr' => ['rows' => '10'],
                'group' => 'no-group'
            ])
        ;
    }
}