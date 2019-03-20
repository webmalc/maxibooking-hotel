<?php

namespace MBH\Bundle\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\UserBundle\Document\AuthorizationToken;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

class UserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public const USER_MANAGER = 'manager';
    public const USER_L_MANAGER = 'lmanager';
    public const USER_ADMIN = 'admin';

    const SANDBOX_USERNAME = 'demo';
    const SANDBOX_USER_TOKEN = 'some_token_for_sandbox_user';
    //TODO: Вернуть на 'mb'
    const MB_USER_USERNAME = 'mb';

    const USERS = [
        'user-admin' => [
            'username' => self::USER_ADMIN,
            'email' => 'admin@example.com',
            'role' => 'ROLE_SUPER_ADMIN',
            'password' => self::USER_ADMIN
        ],
        'user-manager' => [
            'username' => self::USER_MANAGER,
            'email' => 'manager@example.com',
            'role' => 'ROLE_BASE_USER',
            'group' => 'group-medium_manager',
            'password' => self::USER_MANAGER,
        ],
        'user-lmanager' => [
            'username' => self::USER_L_MANAGER,
            'email' => 'manager@example.com',
            'role' => 'ROLE_BASE_USER',
            'group' => 'group-junior_manager',
            'password' => self::USER_L_MANAGER,
            'hotels' => true
        ],
        'user-demo' => [
            'username' => self::SANDBOX_USERNAME,
            'email' => 'mb-error@maxi-booking.com',
            'role' => 'ROLE_SUPER_ADMIN',
            'password' => 'demo'
        ],
        'user-mb' => [
            'username' => self::MB_USER_USERNAME,
            'email' => 'mb-error@maxi-booking.com',
            'role' => 'ROLE_SUPER_ADMIN'
        ]
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $repo = $manager->getRepository('MBHUserBundle:User');
        $notificationTypes = $manager->getRepository('MBHBaseBundle:NotificationType')->getStuffType()->toArray();
        if (!count($repo->findAll())) {
            foreach (self::USERS as $key => $userData) {
                if (in_array($key, ['user-manager', 'user-demo'])
                    && !in_array($this->container->get('kernel')->getEnvironment(), ['dev', 'test', 'sandbox'])
                ) {
                    continue;
                }

                $password = $key === 'user-mb' ? $this->container->getParameter('mb_user_pwd') : $userData['password'];
                $user = new User();
                $user->setUsername($userData['username'])
                    ->setEmail($userData['email'])
                    ->addRole($userData['role'])
                    ->setPlainPassword($password)
                    ->setEnabled(true)
                    ->setLocked(false);

                if ($userData['hotels'] ?? false) {
                    $hotels = $manager->getRepository(Hotel::class)->findAll();
                    $user->setHotels($hotels);
                }

                if (isset($userData['group'])) {
                    $user->addGroup($this->getReference($userData['group']));
                }
                $user->setAllowNotificationTypes($notificationTypes);

                if ($userData['username'] === self::SANDBOX_USERNAME) {
                    $apiToken = (new AuthorizationToken())
                        ->setExpiredAt(new \DateTime('+ 1 day'))
                        ->setToken(self::SANDBOX_USER_TOKEN);
                    $user->setApiToken($apiToken);
                }

                $manager->persist($user);
                $manager->flush();

                $this->setReference($key, $user);
            }
        }
    }

    public function getOrder()
    {
        return 1;
    }
}
