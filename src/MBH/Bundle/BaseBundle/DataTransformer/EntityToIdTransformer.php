<?php

namespace MBH\Bundle\BaseBundle\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityToIdTransformer
 * @package MBH\Bundle\PackageBundle\DataTransformer
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @link https://gist.github.com/bjo3rnf/4061232
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    public function __construct(\Doctrine\ODM\MongoDB\DocumentManager $documentManager, $className)
    {
        $this->documentManager = $documentManager;
        $this->className = $className;
    }

    /**
     * @param object $entity
     * @return string|int
     */
    public function transform($entity)
    {
        if (null === $entity) {
            return;
        }

        return $entity->getId();
    }

    /**
     * @param string|int $id
     * @return null|object
     * @throws \Doctrine\ODM\MongoDB\LockException
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $entity = $this->documentManager->getRepository($this->className)->find($id);

        if (null === $entity) {
            throw new TransformationFailedException();
        }

        return $entity;
    }
}