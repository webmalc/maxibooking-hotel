<?php

namespace MBH\Bundle\CashBundle\Form;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\CashBundle\Document\CashDocumentArticle;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class NewCashDocumentType
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class NewCashDocumentType extends CashDocumentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('organizationPayer', 'text', [
                'label' => 'form.cashDocumentType.organization',
                'required' => false
            ])
            ->add('touristPayer', 'text', [
                'label' => 'form.cashDocumentType.tourist',
                'required' => false
            ])
            ->add('payer_select', 'hidden', [
                'required' => false,
                'mapped' => false,
            ]);
        $builder->get('organizationPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Organization::class));
        $builder->get('touristPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Tourist::class));

        $builder->add('article', 'document', [
            'required' => false,
            'class' => CashDocumentArticle::class,
            'empty_value' => '',
            'label' => 'form.cashDocumentType.article',
            'property' => function (CashDocumentArticle $article) {
                return $article->getCode() . ' ' . $article->getTitle();
            },
            'query_builder' => function (DocumentRepository $repository) {
                return $repository->createQueryBuilder()->sort(['code' => 1]);
            }
        ]);
    }
}