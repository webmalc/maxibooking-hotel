<?php

namespace MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable\Filter\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Filter\BsonFilter;
use MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable\DisableableSubscriber;

class DisableableFilter extends BsonFilter
{
    protected $listener;
    protected $documentManager;
    protected $disabled = array();

    /**
     * Gets the criteria array to add to a query.
     *
     * If there is no criteria for the class, an empty array should be returned.
     *
     * @param ClassMetadata $targetDocument
     * @return array
     */
    public function addFilterCriteria(ClassMetadata $targetDocument)
    {
        $class = $targetDocument->getName();
        if (array_key_exists($class, $this->disabled) && $this->disabled[$class] === true) {
            return [];
        } elseif (array_key_exists($targetDocument->rootDocumentName, $this->disabled)
            && $this->disabled[$targetDocument->rootDocumentName] === true
        ) {
            return [];
        }

        $config = $this->getListener()->getConfiguration($this->getDocumentManager(), $targetDocument->name);

        if (!isset($config['disableable']) || !$config['disableable']) {
            return array();
        }

        $column = $targetDocument->fieldMappings[$config['fieldName']];

        return [
            $column['fieldName'] => true
        ];
    }

    protected function getListener()
    {
        if ($this->listener === null) {
            $em = $this->getDocumentManager();
            $evm = $em->getEventManager();

            foreach ($evm->getListeners() as $listeners) {
                foreach ($listeners as $listener) {
                    if ($listener instanceof DisableableSubscriber) {
                        $this->listener = $listener;

                        break 2;
                    }
                }
            }

            if ($this->listener === null) {
                throw new \RuntimeException('Listener "DisableableListener" was not added to the EventManager!');
            }
        }

        return $this->listener;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        if ($this->documentManager === null) {
            $reflProp = new \ReflectionProperty('Doctrine\ODM\MongoDB\Query\Filter\BsonFilter', 'dm');
            $reflProp->setAccessible(true);
            $this->documentManager = $reflProp->getValue($this);
        }

        return $this->documentManager;
    }

    public function disableForDocument($class)
    {
        $this->disabled[$class] = true;
    }

    public function enableForDocument($class)
    {
        $this->disabled[$class] = false;
    }
}