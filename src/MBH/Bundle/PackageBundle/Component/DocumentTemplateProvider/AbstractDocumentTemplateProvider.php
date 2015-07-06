<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateProvider;


abstract class AbstractDocumentTemplateProvider
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param array $formParams
     * @return string
     */
    public abstract function getTemplate(array $formParams = []);
}