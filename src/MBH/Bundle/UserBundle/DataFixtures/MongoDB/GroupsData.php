<?php
namespace MBH\Bundle\UserBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MBH\Bundle\UserBundle\Document\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

class GroupsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private function groups()
    {
        $translator = $this->container->get('translator');

        return [
                'admin' => [
                    'title' => $translator->trans('restaurantbundle.controller.system_administrator'),
                'roles' => ['ROLE_ADMIN']
            ],
            'hotel_admin' => [
            'title' => $translator->trans('restaurantbundle.controller.hotel_administrator'),
            'roles' => [
                'ROLE_HOTEL', 'ROLE_CITY', 'ROLE_LOGS', 'ROLE_CASH', 'ROLE_CLIENT_CONFIG',
                'ROLE_DOCUMENT_TEMPLATE', 'ROLE_HOUSING', 'ROLE_ROOM', 'ROLE_ROOM_TYPE', 'ROLE_TASK_MANAGER',
                'ROLE_MANAGER', 'ROLE_OVERVIEW', 'ROLE_PRICE_CACHE', 'ROLE_RESTRICTION', 'ROLE_ROOM_CACHE',
                'ROLE_SERVICE', 'ROLE_SERVICE_CATEGORY', 'ROLE_TARIFF', 'ROLE_SPECIAL', 'ROLE_CHANNEL_MANAGER',
                'ROLE_ONLINE_FORM', 'ROLE_POLLS', 'ROLE_REPORTS', 'ROLE_PACKAGE', 'ROLE_SOURCE', 'ROLE_PROMOTION',
                'ROLE_ROOM_TYPE_CATEGORY', 'ROLE_WORK_SHIFT', 'ROLE_RESTAURANT_MAIN_MANAGER'
            ]
        ],
            'analytics' => [
            'title' => $translator->trans('restaurantbundle.controller.analitic'),
            'roles' => [
                'ROLE_TARIFF_VIEW', 'ROLE_SPECIAL_VIEW','ROLE_SERVICE_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW',
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
            'title' => $translator->trans('restaurantbundle.controller.buhgalter'),
            'roles' => [
                'ROLE_CASH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_PACKAGE_EDIT_ALL',
                'ROLE_DOCUMENTS_GENERATOR'
            ]
        ],
            'booking_agent' => [
            'title' => $translator->trans('restaurantbundle.controller.turagent'),
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW'

            ]
        ],
            'junior_manager' => [
            'title' => $translator->trans('restaurantbundle.controller.little_manager'),
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD', 'ROLE_HOTEL_VIEW'

            ]
        ],
            'medium_manager' => [
            'title' => $translator->trans('restaurantbundle.controller.manager'),
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD'

            ]
        ],
            'senior_manager' => [
            'title' => $translator->trans('restaurantbundle.controller.senior_manager'),
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE_ALL',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_CASH_VIEW', 'ROLE_CASH_NEW', 'ROLE_CASH_EDIT', 'ROLE_CASH_DELETE',
                'ROLE_SERVICE', 'ROLE_SERVICE_CATEGORY', 'ROLE_PRICE_CACHE', 'ROLE_RESTRICTION',
                'ROLE_ROOM_CACHE', 'ROLE_PACKAGE_EDIT_ALL', 'ROLE_SERVICES_REPORT',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_INDIVIDUAL_PROMOTION_ADD', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD',
                'ROLE_FORCE_BOOKING'
            ]
        ],
            'porter' => [
            'title' => $translator->trans('restaurantbundle.controller.portie'),
            'roles' => [
                'ROLE_TOURIST', 'ROLE_ORGANIZATION', 'ROLE_CITY', 'ROLE_PACKAGE_DELETE_ALL',
                'ROLE_SEARCH', 'ROLE_PACKAGE_VIEW', 'ROLE_PACKAGE_VIEW_ALL', 'ROLE_PACKAGE_NEW',
                'ROLE_ORDER_EDIT', 'ROLE_PACKAGE_EDIT', 'ROLE_ORDER_PAYER', 'ROLE_PACKAGE_GUESTS',
                'ROLE_PACKAGE_SERVICES', 'ROLE_PACKAGE_ACCOMMODATION', 'ROLE_ORDER_DOCUMENTS',
                'ROLE_ORDER_CASH_DOCUMENTS', 'ROLE_ORDER_AUTO_CONFIRMATION',
                'ROLE_PRICE_CACHE_VIEW', 'ROLE_RESTRICTION_VIEW', 'ROLE_ROOM_CACHE_VIEW', 'ROLE_SERVICE_VIEW',
                'ROLE_CASH_VIEW', 'ROLE_CASH_NEW', 'ROLE_CASH_EDIT', 'ROLE_CASH_DELETE',
                'ROLE_SERVICE_VIEW', 'ROLE_PACKAGE_EDIT_ALL', 'ROLE_SERVICES_REPORT',
                'ROLE_PORTER_REPORT', 'ROLE_ACCOMMODATION_REPORT', 'ROLE_ROOMS_REPORT',
                'ROLE_DOCUMENTS_GENERATOR', 'ROLE_PROMOTION_ADD', 'ROLE_DISCOUNT_ADD', 'ROLE_ROOM_STATUS_EDIT'
            ]
        ],
            'staff' => [
            'title' => $translator->trans('restaurantbundle.controller.servive_staff'),
            'roles' => [
                'ROLE_STAFF'
            ]
        ],
            'restaurant_senior' => [
            'title' => $translator->trans('restaurantbundle.controller.restoran_senior_manager'),
            'roles' => [
                'ROLE_RESTAURANT_SENIOR_MANAGER'
            ]
        ],
            'restaurant_junior' => [
            'title' => $translator->trans('restaurantbundle.controller.restoran_manager'),
            'roles' => [
                'ROLE_RESTAURANT_MANAGER'
            ]
        ],
            'warehouse' => [
            'title' => $translator->trans('restaurantbundle.controller.warehouse'),
            'roles' => [
                'ROLE_WAREHOUSE'
            ]
        ]
        ];
    }

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

        foreach($this->groups() as $code => $info) {
            if (!in_array($code, $codes)) {
                $group = new Group($info['title'], $code, $info['roles']);
                $manager->persist($group);
                $manager->flush();
                $this->setReference('group-' . $code, $group);
            }
        }

    }

    public function getOrder()
    {
        return -1;
    }
}
