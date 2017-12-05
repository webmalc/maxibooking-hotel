<?php

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class Invoice
{
    /**
     * @var DocumentTemplate
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\ClientBundle\Document\DocumentTemplate")
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