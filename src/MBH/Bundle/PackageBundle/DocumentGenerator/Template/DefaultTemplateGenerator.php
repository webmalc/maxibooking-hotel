<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultDocumentTemplateGenerator for Package

 */
class DefaultTemplateGenerator extends TemplateGenerator
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @param array $formData
     * @return array
     */
    protected function prepareParams(array $formData)
    {
        $documentTypes = $this->container->get('mbh.fms_dictionaries')->getDocumentTypes();

        $formData['entity'] = $formData['package'];
        $formData['formParams'] = $formData;
        $formData['documentTypes'] = $documentTypes;

        return $formData;
    }


    /**
     * @return Response
     */
    public function generateResponse(array $formData)
    {
        $response = parent::generateResponse($formData);
        $contentDisposition = 'filename="'.$this->type.'_'.$formData['package']->getNumberWithPrefix().'.pdf"';//'attachment;
        $response->headers->set('Content-Disposition', $contentDisposition);
        return $response;
    }
}