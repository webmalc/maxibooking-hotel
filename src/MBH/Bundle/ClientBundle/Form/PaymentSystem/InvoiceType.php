<?php
/**
 * Created by PhpStorm.
 * Date: 23.08.18
 */

namespace MBH\Bundle\ClientBundle\Form\PaymentSystem;


use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\ClientBundle\Document\PaymentSystem\Invoice;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Form\FormBuilderInterface;

class InvoiceType extends PaymentSystemType
{
    public static function getSourceDocument(): PaymentSystemDocument
    {
        return new Invoice();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'invoiceDocument',
            DocumentType::class,
            $this->addCommonAttributes(
                [
                    'label' => 'form.clientPaymentSystemType.invoice_document.label',
                    'class' => DocumentTemplate::class,
                ]
            )
        );
    }
}