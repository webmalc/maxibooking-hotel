<?php

namespace MBH\Bundle\BaseBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TimestampableListener
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class TranslatableListener extends \Gedmo\Translatable\TranslatableListener implements ContainerAwareInterface
{
    private $container;
    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

}