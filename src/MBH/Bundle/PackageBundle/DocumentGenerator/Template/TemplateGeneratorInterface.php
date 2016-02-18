<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;

/**
 * Interface DocumentTemplateGeneratorInterface

 */
interface TemplateGeneratorInterface extends DocumentResponseGeneratorInterface
{
    /**
     * @param array $formData
     * @return string
     */
    public function getTemplate(array $formData);
}