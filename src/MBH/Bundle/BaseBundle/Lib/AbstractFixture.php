<?php

namespace MBH\Bundle\BaseBundle\Lib;

use Doctrine\Common\DataFixtures\AbstractFixture as Base;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractFixture extends Base implements ContainerAwareInterface
{
    use ContainerAwareTrait;
 
    /**
     * return environment
     *
     * @return string
     */
    protected function getEnv(): string
    {
        return $this->container->get('kernel')->getEnvironment();
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        if (in_array($this->getEnv(), $this->getEnvs())) {
            $this->doLoad($manager);
        }
    }
    
    /**
     * get environments for fixture
     *
     * @return array
     */
    protected function getEnvs(): array
    {
        return ['test', 'dev', 'prod', 'sandbox'];
    }
}
