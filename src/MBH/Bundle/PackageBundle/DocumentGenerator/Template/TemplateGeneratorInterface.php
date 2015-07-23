<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;

/**
 * Interface DocumentTemplateGeneratorInterface
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
interface TemplateGeneratorInterface extends DocumentResponseGeneratorInterface
{
    /**
     * @return string
     */
    public function getTemplate();
}