<?php

namespace MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable\Mapping\Driver;

use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    /**
     * Annotation to define that this object is loggable
     */
    const DISABLEABLE = 'MBH\Bundle\BaseBundle\Lib\Gedmo\Disableable\Disableable';

    /**
     * Read extended metadata configuration for
     * a single mapped class
     *
     * @param object $meta
     * @param array $config
     *
     * @return void
     */
    public function readExtendedMetadata($meta, array &$config)
    {
        $class = $this->getMetaReflectionClass($meta);
        if ($class !== null && $annot = $this->reader->getClassAnnotation($class, self::DISABLEABLE)) {
            $config['disableable'] = true;

            $config['fieldName'] = $annot->fieldName;
        }

        $this->validateFullMetadata($meta, $config);
    }
}