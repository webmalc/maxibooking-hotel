<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateGenerator;

/**
 * Class AbstractDocumentTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
abstract class AbstractDocumentTemplateGenerator
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
     * @return string
     */
    public abstract function getTemplate();
}