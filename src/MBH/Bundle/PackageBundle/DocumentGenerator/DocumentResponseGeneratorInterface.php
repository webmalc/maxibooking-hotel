<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface DocumentResponseGeneratorInterface
 * @package MBH\Bundle\PackageBundle\Component
 */
interface DocumentResponseGeneratorInterface
{
    /**
     * @param array $formData
     * @return Response
     */
    public function generateResponse(array $formData);
}