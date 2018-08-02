<?php

namespace MBH\Bundle\CashBundle\Form;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\BaseBundle\Form\Extension\OrderedTrait;
use MBH\Bundle\CashBundle\Document\CashDocumentArticle;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\ClientBundle\Service\ClientManager;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NewCashDocumentType extends CashDocumentType
{
    use OrderedTrait;

    private $clientManager;

    public function __construct(TranslatorInterface $translator, DocumentManager $dm, array $methods, array $operations, ClientManager $clientManager, ClientConfigManager $clientConfigManager) {
        parent::__construct($translator, $dm, $methods, $operations, $clientConfigManager);
        $this->clientManager = $clientManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('payer_select')
            ->remove('method')
        ;
        $builder->get('organizationPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Organization::class));
        $builder->get('touristPayer')->addViewTransformer(new EntityToIdTransformer($this->documentManager, Tourist::class));

        if ($this->clientManager->isRussianClient()) {
            $builder->add('article', DocumentType::class, [
                'required' => false,
                'class' => CashDocumentArticle::class,
                'placeholder' => '',
                'label' => 'form.cashDocumentType.article',
                'group_by' => 'parent',
                'choice_label' => function (CashDocumentArticle $article) {
                    return $article->getCode() . ' ' . $article->getTitle();
                },
                //'attr' => ['class' => 'plain-html'],
                'query_builder' => function (DocumentRepository $repository) {
                    return $repository->createQueryBuilder()->field('parent')->exists(true)->sort(['code' => 1]);
                },
            ]);
        }
    }

    public function getFieldsOrder()
    {
        return [
            'organizationPayer',
            'touristPayer',
            'operation',
            'article',
        ];
    }
}