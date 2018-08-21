<?php

namespace MBH\Bundle\CashBundle\Form;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\DataTransformer\EntityToIdTransformer;
use MBH\Bundle\BaseBundle\Form\Extension\InvertChoiceType;
use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Service\ClientConfigManager;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CashDocumentType
 */
class CashDocumentType extends AbstractType
{
    private $translator;

    /** @var  DocumentManager */
    protected $documentManager;
    /** @var  array */
    private $methods;
    private $operations;
    private $clientConfigManager;

    public function __construct(TranslatorInterface $translator, DocumentManager $dm, array $methods, array $operations, ClientConfigManager $clientConfigManager)
    {
        $this->translator = $translator;
        $this->documentManager = $dm;
        $this->methods = $methods;
        $this->operations = $operations;
        $this->clientConfigManager = $clientConfigManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $payers = [];
        $clientConfig = $this->clientConfigManager->fetchConfig();;
        /** @var CashDocument $cashDocument */
        $cashDocument = $builder->getData();

        /** @var PayerInterface $payer */
        foreach ($options['payers'] as $payer) {
            $text = $payer->getName();
            if ($payer instanceof Organization) {
                $prefix = 'org';
                $text .= ' (' . $this->translator->trans('form.cashDocumentType.inn') . ' ' . $payer->getInn() . ') ' . $payer->getDirectorFio();
            } elseif ($payer instanceof Tourist) {
                $prefix = 'tourist';
                $text .= $payer->getBirthday() ? ' ' . $payer->getBirthday()->format('d.m.Y') : '';
            } else {
                throw new \Exception();
            }

            $payers[$prefix . '_' . $payer->getId()] = $text;
        }

        $translatedMethods = [];
        foreach ($this->methods as $index => $methodName) {
            $translatedMethods[$index] = $this->translator->trans($methodName, [], 'MBHCashBundle');
        }

        $builder
            ->add('payer_select', InvertChoiceType::class, [
                'label' => 'form.cashDocumentType.payer',
                'required' => true,
                'mapped' => false,
                'data' => $options['payer'] ? $options['payer'] : null,
                'group' => $options['groupName'],
                'choices' => $payers,
                'attr' => [
                    'placeholder' => 'form.cashDocumentType.placeholder_fio',
                    'style' => 'min-width: 500px',
                ],
                'placeholder' => ''
            ])
            ->add('organizationPayer', HiddenType::class, [
                'required' => false,
            ])
            ->add('touristPayer', HiddenType::class, [
                'required' => false,
            ])
            ->add('operation', InvertChoiceType::class, [
                'label' => 'form.cashDocumentType.operation_type',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'group' => $options['groupName'],
                'choices' => $this->operations
            ])
            ->add('total', TextType::class, [
                'label' => 'form.cashDocumentType.sum',
                'required' => true,
                'group' => $options['groupName'],
                'attr' => ['class' => 'price-spinner'],
            ])
            ->add('method', InvertChoiceType::class, [
                'label' => 'form.cashDocumentType.payment_way',
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'group' => $options['groupName'],
                'choices' => $translatedMethods
            ])
            ->add('document_date', DateType::class, [
                'label' => 'form.cashDocumentType.document_date',
                'required' => true,
                'group' => $options['groupName'],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add(
                'isPaid', CheckboxType::class, [
                'label' => 'form.cashDocumentType.is_paid',
                'required' => false,
                'group' => $options['groupName'],
            ])
            ->add(
                'isSendMail', CheckboxType::class, [
                'label' => 'form.cashDocumentType.is_send_mail.label',
                'help' => 'form.cashDocumentType.is_send_mail.help',
                'required' => false,
                'group' => $options['groupName'],
                'data' => !is_null($cashDocument) && !is_null($cashDocument->isSendMail())
                    ? $cashDocument->isSendMail()
                    : $clientConfig->isSendMailAtPaymentConfirmation()
            ])
            ->add('paid_date', DateType::class, [
                'label' => 'form.cashDocumentType.paid_date',
                'required' => false,
                'group' => $options['groupName'],
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
            ])
            ->add('number', $options['number'] ? TextType::class : HiddenType::class, [
                'label' => 'form.cashDocumentType.number',
                'group' => $options['groupName'],
                'required' => true,
            ])
            ->add('note', TextareaType::class, [
                'label' => 'form.cashDocumentType.comment',
                'group' => $options['groupName'],
                'required' => false,
            ]);

        $builder->get('organizationPayer')->addModelTransformer(new EntityToIdTransformer($this->documentManager, 'MBHPackageBundle:Organization'));
        $builder->get('touristPayer')->addModelTransformer(new EntityToIdTransformer($this->documentManager, 'MBHPackageBundle:Tourist'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MBH\Bundle\CashBundle\Document\CashDocument',
            'groupName' => null,
            'payer' => null,
            'payers' => [],
            'number' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'mbh_bundle_cash_cash_document';
    }
}
