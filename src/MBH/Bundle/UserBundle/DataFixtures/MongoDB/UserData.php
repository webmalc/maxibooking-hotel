<?php

namespace MBH\Bundle\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

class UserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    const USERS = [
        'user-admin' => [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'role' => 'ROLE_SUPER_ADMIN',
            'password' => 'admin'
        ],
        'user-manager' => [
            'username' => 'manager',
            'email' => 'manager@example.com',
            'role' => 'ROLE_USER',
            'group' => 'group-medium_manager',
            'password' => 'manager',
        ],
        'user-mb' => [
            'username' => 'mb',
            'email' => 'mb@example.com',
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
                if ($key === 'user-manager' && !$this->container->get('kernel')->isDevEnv()) {
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

                if (isset($userData['group'])) {
                    $user->addGroup($this->getReference($userData['group']));
                }
                $user->setAllowNotificationTypes($notificationTypes);

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
