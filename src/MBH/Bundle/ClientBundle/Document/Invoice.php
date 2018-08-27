<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\ClientBundle\Lib\PaymentSystemDocument;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ODM\EmbeddedDocument
 */
class Invoice extends PaymentSystemDocument
{
    /**
     * @var DocumentTemplate
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @Assert\Type(type="MBH\Bundle\ClientBundle\Document\DocumentTemplate")
     * @Assert\NotNull()
     */
    private $invoiceDocument;

    /**
     * @return DocumentTemplate
     */
    public function getInvoiceDocument(): ?DocumentTemplate
    {
        return $this->invoiceDocument;
    }

    /**
     * @param DocumentTemplate $invoiceDocument
     * @return Invoice
     */
    public function setInvoiceDocument(DocumentTemplate $invoiceDocument): Invoice
    {
        $this->invoiceDocument = $invoiceDocument;

        return $this;
    }
}