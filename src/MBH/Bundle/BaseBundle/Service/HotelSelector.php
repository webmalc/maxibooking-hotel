<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * HotelSelector service
 */
class HotelSelector
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Hotel $hotel
     * @param User|null $user
     * @return bool
     */
    public function checkPermissions(Hotel $hotel, User $user = null)
    {
        if ('cli' === PHP_SAPI) {
            return true;
        }
        if (!$user && !$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return true;
        }

        $user ?: $user = $this->container->get('security.token_storage')->getToken()->getUser();

        // Is admin?
        $token = new UsernamePasswordToken($user, 'none', 'none', $user->getRoles());

        if ($this->container->get('kernel')->getEnvironment() == 'prod' && $this->container->has('security.access.decision_manager')) {
            $decision_manager = $this->container->get('security.access.decision_manager');
        } else {
            $decision_manager = $this->container->get('debug.security.access.decision_manager');
        }

        if ($decision_manager->decide($token, array('ROLE_ADMIN'))) {
            return true;
        }

        // Can edit hotel?
        $objectIdentity = ObjectIdentity::fromDomainObject($hotel);
        $securityIdentity = new UserSecurityIdentity($user, 'MBH\Bundle\UserBundle\Document\User');
        $aclProvider = $this->container->get('security.acl.provider');

        try {
            $acl = $aclProvider->findAcl($objectIdentity);
        } catch (AclNotFoundException $e) {
            return false;
        }
        try {
            return $acl->isGranted([MaskBuilder::MASK_MASTER], [$securityIdentity], false);
        } catch (NoAceFoundException $e) {
            return false;
        }
    }

    /**
     * @return null|\MBH\Bundle\HotelBundle\Document\Hotel
     */
    public function getSelected()
    {
        $session = $this->container->get('session');
        $id = $session->get('selected_hotel_id');

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotelRepository = $dm->getRepository('MBHHotelBundle:Hotel');
        if (!empty($id)) {
            $hotel = $hotelRepository->find($id);
            if ($hotel && $this->checkPermissions($hotel)) {
                return $hotel;
            }
            $session->remove('selected_hotel_id');
        }

        // Select first hotel
        $hotels = $hotelRepository->createQueryBuilder()
            ->sort('isDefault', 'desc')
            ->getQuery()
            ->execute();

        foreach ($hotels as $hotel) {
            if ($hotel && $this->checkPermissions($hotel)) {
                $session->set('selected_hotel_id', (string)$hotel->getId());
                return $hotel;
            }
        }
        return null;
    }


    /**
     * @param string $id
     * @return \MBH\Bundle\HotelBundle\Document\Hotel
     */
    public function setSelected($id)
    {
        $session = $this->container->get('session');
        $session->remove('selected_hotel_id');

        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $hotel = $dm->getRepository('MBHHotelBundle:Hotel')->find($id);

        if ($hotel && $this->checkPermissions($hotel)) {
            $session->set('selected_hotel_id', (string)$hotel->getId());
            return $hotel;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getSelectedPackages()
    {
        $hotel = $this->getSelected();

        if (!$hotel) {
            return [];
        }

        $dm = $this->container->get('doctrine_mongodb')->getManager();
        $dm->getFilterCollection()->disable('softdeleteable');

        $packages = $dm->getRepository('MBHPackageBundle:Package')->createQueryBuilder('s')
            ->field('roomType.id')->in($this->container->get('mbh.helper')->toIds($hotel->getRoomTypes()))
            ->getQuery()
            ->execute();
        $dm->getFilterCollection()->enable('softdeleteable');

        return $packages;
    }

}
