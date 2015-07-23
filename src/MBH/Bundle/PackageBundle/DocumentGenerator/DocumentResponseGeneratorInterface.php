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
     * @return Response
     */
    public function generateResponse();

    /**
     * @param array $formParams
     */
    public function setFormParams(array $formParams);
}