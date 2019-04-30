<?php

namespace MBH\Bundle\BaseBundle\Service;

use MBH\Bundle\BaseBundle\Security\HotelVoter;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Security;

/**
 * HotelSelector service
 */
class HotelSelector
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * @var Security
     */
    private $security;

    public function __construct(ContainerInterface $container, Security $security)
    {
        $this->container = $container;
        $this->security = $security;
    }

    /**
     * @param Hotel $hotel
     * @param User|null $user
     * @return bool
     */
    public function checkPermissions(Hotel $hotel, User $user = null)
    {
        if (!$this->isUserAuthenticated()) {
            if ($user) {
                $token = new UsernamePasswordToken($user, 'none', 'main', $user->getRoles());
                if ($this->container->get('kernel')->getEnvironment() === 'prod' && $this->container->has('security.access.decision_manager')) {
                    $decisionManager = $this->container->get('security.access.decision_manager');
                } else {
                    $decisionManager = $this->container->get('debug.security.access.decision_manager');
                }

                return $decisionManager->decide($token, [HotelVoter::ACCESS], $hotel)
                    || $decisionManager->decide($token, ['ROLE_ADMIN']);
            }

            return true;
        }

        return $this->security->isGranted('ROLE_ADMIN')
            || $this->security->isGranted(HotelVoter::ACCESS, $hotel);
    }

    private function isUserAuthenticated(): bool
    {
        try {
            return $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return PHP_SAPI !== 'cli';
        }

    }

    /**
     * @return null|\MBH\Bundle\HotelBundle\Document\Hotel
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
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
