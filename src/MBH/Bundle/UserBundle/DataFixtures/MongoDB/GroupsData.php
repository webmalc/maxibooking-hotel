<?php
namespace MBH\Bundle\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GroupsData implements FixtureInterface, ContainerAwareInterface
{
    const GROUPS = [
        'admin' => [
            'title' => 'Администратор системы',
            'roles' => ['ROLE_ADMIN']
        ],
        'hotel_admin' => [
            'title' => 'Администратор отеля',
            'roles' => [
                'ROLE_HOTEL', 'ROLE_CITY', 'ROLE_LOGS', 'ROLE_CASH', 'ROLE_CLIENT_CONFIG',
                'ROLE_DOCUMENT_TEMPLATE', 'ROLE_HOUSING', 'ROLE_ROOM', 'ROLE_ROOM_TYPE', 'ROLE_TASK_MANAGER',
                'ROLE_MANAGER', 'ROLE_OVERVIEW', 'ROLE_PRICE_CACHE', 'ROLE_RESTRICTION', 'ROLE_ROOM_CACHE',
                'ROLE_SERVICE', 'ROLE_SERVICE_CATEGORY', 'ROLE_TARIFF', 'ROLE_CHANNEL_MANAGER',
                'ROLE_ONLINE_FORM', 'ROLE_POLLS', 'ROLE_REPORTS', 'ROLE_PACKAGE', 'ROLE_SOURCE', 'ROLE_PROMOTION',
                'ROLE_ROOM_TYPE_CATEGORY', 'ROLE_WORK_SHIFT'
            ]
        ],
        'analytics' => [
            'title' => 'Аналитик',
            'roles' => [
                'ROLE_TARIFF_VIEW', 'ROLE_SERVICE_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_TASK_TYPE_CATEGORY_VIEW',
                'ROLE_TASK_VIEW', 'ROLE_TASK_OWN_VIEW', 'ROLE_SOURCE_VIEW', 'ROLE_ORGANIZATION_VIEW',
                'ROLE_TOURIST_VIEW', 'ROLE_PACKAGE_VIEW', 'ROLE_LOGS',
                'ROLE_HOTEL_VIEW', 'ROLE_CITY_VIEW', 'ROLE_CASH_VIEW',
                'ROLE_ROOM_TYPE_VIEW', 'ROLE_ROOM_VIEW',
                'ROLE_ANALYTICS', 'ROLE_PORTER_REPORT', 'ROLE_ACCOMMODATION_REPORT', 'ROLE_SERVICES_REPORT', 'ROLE_MANAGERS_REPORT',
                'ROLE_POLLS_REPORT', 'ROLE_ROOMS_REPORT', 'ROLE_ORGANIZATION_VIEW', 'ROLE_ORGANIZATION_VIEW'
            ]
        ],
        'bookkeeper' => [
            'title' => 'Бухгалтер',
            'roles' => [
                'ROLE_CASH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_EDIT_ALL',
                'ROLE_DOCUMENTS_GENERATOR'
            ]
        ],
        'booking_agent' => [
            'title' => 'Турагент',
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_DOCS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW'

            ]
        ],
        'junior_manager' => [
            'title' => 'Младший менеджер',
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_DOCS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD'
                
            ]
        ],
        'medium_manager' => [
            'title' => 'Менеджер',
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_DOCS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD'

            ]
        ],
        'senior_manager' => [
            'title' => 'Старший менеджер',
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE_ALL',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_DOCS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_CASH_VIEW', 'ROLE_CASH_NEW', 'ROLE_CASH_EDIT', 'ROLE_CASH_DELETE',
                'ROLE_SERVICE', 'ROLE_SERVICE_CATEGORY', 'ROLE_PRICE_CACHE', 'ROLE_RESTRICTION',
                'ROLE_ROOM_CACHE', 'ROLE_PACKAGE_EDIT_ALL', 'ROLE_SERVICES_REPORT',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_INDIVIDUAL_PROMOTION_ADD', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD',
                'ROLE_FORCE_BOOKING'
            ]
        ],
        'porter' => [
            'title' => 'Портье',
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE_ALL',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_DOCS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_CASH_VIEW', 'ROLE_CASH_NEW', 'ROLE_CASH_EDIT', 'ROLE_CASH_DELETE',
                'ROLE_SERVICE_VIEW', 'ROLE_PACKAGE_EDIT_ALL', 'ROLE_SERVICES_REPORT',
                'ROLE_PORTER_REPORT', 'ROLE_ACCOMMODATION_REPORT', 'ROLE_ROOMS_REPORT',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD', 'ROLE_ROOM_STATUS_EDIT'
            ]
        ],
        'staff' => [
            'title' => 'Обслуживающий персонал',
            'roles' => [
                'ROLE_STAFF'
            ]
        ],
        'restaurant' => [
            'title' => 'Ресторан',
            'roles' => [
                'ROLE_RESTAURANT'
            ]
        ],
        'warehouse' => [
            'title' => 'Склад',
            'roles' => [
                'ROLE_WAREHOUSE'
            ]
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
        $docs = $manager->getRepository('MBHUserBundle:Group')->findBy(['code' => ['$ne' => null]]);
        $codes = $this->container->get('mbh.helper')->toIds($docs, 'getCode');

        foreach(self::GROUPS as $code => $info) {
            if (!in_array($code, $codes)) {
                $manager->persist(new Group($info['title'], $code, $info['roles']));
            }
        }
        $manager->flush();
    }
}
