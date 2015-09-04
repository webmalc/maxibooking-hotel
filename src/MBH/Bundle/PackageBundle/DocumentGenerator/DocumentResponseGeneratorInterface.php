<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface DocumentResponseGeneratorInterface
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
interface DocumentResponseGeneratorInterface
{
    /**
     * @param array $formData
     * @return Response
     */
    public function generateResponse(array $formData);
}