<?php

namespace MBH\Bundle\BaseBundle\Menu;


use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RequestContentIdentityVoter
 * @link https://github.com/KnpLabs/KnpMenuBundle/issues/122
 */
class RequestContentIdentityVoter implements VoterInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * @param ItemInterface $item
     * @return boolean|null
     */
    public function matchItem(ItemInterface $item)
    {
        if ($item->getUri() === $this->container->get('request_stack')->getCurrentRequest()->getRequestUri()) {
            return true;
        }

        return null;
    }
}