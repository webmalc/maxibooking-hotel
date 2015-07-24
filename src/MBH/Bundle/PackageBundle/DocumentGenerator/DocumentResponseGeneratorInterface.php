<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator;

use Symfony\Component\HttpFoundation\Response;

/**
 * Interface DocumentResponseGeneratorInterface
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
interface DocumentResponseGeneratorInterface
{
    /**
     * @param array $formData
     * @return Response
     */
    public function generateResponse(array $formData);
}