<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;

/**
 * Interface DocumentTemplateGeneratorInterface
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
interface TemplateGeneratorInterface extends DocumentResponseGeneratorInterface
{
    /**
     * @param array $formData
     * @return string
     */
    public function getTemplate(array $formData);
}