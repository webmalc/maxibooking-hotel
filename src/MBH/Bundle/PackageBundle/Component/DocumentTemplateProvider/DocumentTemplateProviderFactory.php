<?php

namespace MBH\Bundle\PackageBundle\Component\DocumentTemplateProvider;

class DocumentTemplateProviderFactory
{
    protected function getExtendedTypes()
    {
        return [
            'confirmation' => 'ConfirmationTemplateProvider'
        ];
    }

    /**
     * @param $type
     * @return DefaultDocumentTemplateProvider
     */
    public function createByType($type)
    {
        $extendedTypes = $this->getExtendedTypes();
        if(array_key_exists($type, $extendedTypes)) {
            $className = __NAMESPACE__.'\\'.$extendedTypes[$type];
            /** @var DefaultDocumentTemplateProvider $provider */
            $provider = new $className($type);
            return $provider;
        }

        return $this->createByDefault($type);
    }

    private function createByDefault($type)
    {
        return new DefaultDocumentTemplateProvider($type);
    }
}