<?php

namespace MBH\Bundle\BaseBundle\EventListener\OnRemoveSubscriber;

use MBH\Bundle\CashBundle\Document\CashDocument;
use MBH\Bundle\ClientBundle\Document\DocumentTemplate;
use MBH\Bundle\HotelBundle\Document\City;
use MBH\Bundle\HotelBundle\Document\Country;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\HotelBundle\Document\Housing;
use MBH\Bundle\HotelBundle\Document\Region;
use MBH\Bundle\HotelBundle\Document\Room;
use MBH\Bundle\HotelBundle\Document\RoomStatus;
use MBH\Bundle\HotelBundle\Document\RoomType;
use MBH\Bundle\HotelBundle\Document\RoomTypeCategory;
use MBH\Bundle\HotelBundle\Document\Task;
use MBH\Bundle\HotelBundle\Document\TaskType;
use MBH\Bundle\HotelBundle\Document\TaskTypeCategory;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Organization;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\PackageAccommodation;
use MBH\Bundle\PackageBundle\Document\PackageService;
use MBH\Bundle\PackageBundle\Document\PackageSource;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PriceBundle\Document\PriceCache;
use MBH\Bundle\PriceBundle\Document\Promotion;
use MBH\Bundle\PriceBundle\Document\Restriction;
use MBH\Bundle\PriceBundle\Document\RoomCache;
use MBH\Bundle\PriceBundle\Document\Service;
use MBH\Bundle\PriceBundle\Document\ServiceCategory;
use MBH\Bundle\PriceBundle\Document\Special;
use MBH\Bundle\PriceBundle\Document\Tariff;
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItem;
use MBH\Bundle\RestaurantBundle\Document\DishOrderItem;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Document\IngredientCategory;
use MBH\Bundle\RestaurantBundle\Document\Table;
use MBH\Bundle\UserBundle\Document\Group;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Document\WorkShift;
use MBH\Bundle\VegaBundle\Document\VegaState;
use MBH\Bundle\WarehouseBundle\Document\Invoice;
use MBH\Bundle\WarehouseBundle\Document\Record;
use MBH\Bundle\WarehouseBundle\Document\WareCategory;
use MBH\Bundle\WarehouseBundle\Document\WareItem;

class DocumentsRelationships
{
    public static function getRelationships()
    {
        return [
            TaskType::class => [
                new Relationship(Task::class, 'type', 'exception.relation_delete.message.task')
            ],
            TaskTypeCategory::class => [
                new Relationship(TaskType::class, 'category', 'exception.relation_delete.message.taskType')
            ],
            RoomTypeCategory::class => [
                new Relationship(RoomType::class, 'category', 'exception.relation_delete.message.roomTypeCategory')
            ],
            RoomStatus::class => [
                new Relationship(Room::class, 'status'),
                new Relationship(TaskType::class, 'roomStatus', 'exception.roomStatus_relation_delete.message.taskType')
            ],
            Hotel::class => [
                new Relationship(DocumentTemplate::class, 'hotel', 'exception.hotel_relation_delete.message.documentTemplate'),
                new Relationship(RoomType::class, 'hotel', 'exception.hotel_relation_delete.message.roomType'),
                new Relationship(RoomTypeCategory::class, 'hotel', 'exception.hotel_relation_delete.message.roomTypeCategory'),
                new Relationship(Room::class, 'hotel','exception.hotel_relation_delete.message.room'),
                new Relationship(Tariff::class, 'hotel', 'exception.hotel_relation_delete.message.tariff'),
                new Relationship(ServiceCategory::class, 'hotel', 'exception.hotel_relation_delete.message.serviceCategory'),
                new Relationship(IngredientCategory::class, 'hotel', 'exception.hotel_relation_delete.message.ingredientCategory'),
                new Relationship(DishMenuCategory::class, 'hotel', 'exception.hotel_relation_delete.message.dishMenuCategory'),
                new Relationship(Housing::class, 'hotel', 'exception.hotel_relation_delete.message.housing'),
                //new Relationship(PriceCache::class, 'hotel', 'exception.hotel_relation_delete.message.priceCache'),
                //new Relationship(Restriction::class, 'hotel', 'exception.hotel_relation_delete.message.restriction'),
                //new Relationship(RoomCache::class, 'hotel', 'exception.hotel_relation_delete.message.roomCache'),
                new Relationship(ServiceCategory::class, 'hotel', 'exception.hotel_relation_delete.message.serviceCategory'),
                new Relationship(DishMenuCategory::class, 'hotel', 'exception.hotel_relation_delete.message.dishMenuCategory'),
                new Relationship(DishOrderItem::class, 'hotel', 'exception.hotel_relation_delete.message.dishOrderItem'),
                new Relationship(IngredientCategory::class, 'hotel', 'exception.hotel_relation_delete.message.ingredientCategory'),
                new Relationship(Table::class, 'hotel', 'exception.hotel_relation_delete.message.table'),
                new Relationship(Invoice::class, 'hotel', 'exception.hotel_relation_delete.message.invoice'),
                new Relationship(Record::class, 'hotel', 'exception.hotel_relation_delete.message.record'),
            ],
            Organization::class => [
                new Relationship(DocumentTemplate::class, 'organization', 'exception.organization_relation_delete.message.documentTemplate'),
                new Relationship(Invoice::class, 'organization', 'exception.organization_relation_delete.message.invoice'),
                new Relationship(Order::class, 'organization', 'exception.organization_relation_delete.message.order'),
                new Relationship(CashDocument::class, 'organizationPayer', 'exception.organization_relation_delete.message.cashDocument')
            ],
            City::class => [
                new Relationship(Housing::class, 'city', 'exception.city_relation_delete.message.housing'),
                new Relationship(Organization::class, 'city', 'exception.city_relation_delete.message.organization')
            ],
            RoomType::class => [
                new Relationship(Room::class, 'roomType', 'exception.roomType_relation_delete.message.room'),
                //new Relationship(PriceCache::class, 'roomType', 'exception.roomType_relation_delete.message.priceCache'),
                //new Relationship(RoomCache::class, 'roomType', 'exception.roomType_relation_delete.message.roomCache'),
                new Relationship(Package::class, 'roomType', 'exception.roomType_relation_delete.message.package')
            ],
            Room::class => [
                new Relationship(Task::class, 'room', 'exception.room_relation_delete.message.task'),
                new Relationship(PackageAccommodation::class, 'accommodation', 'exception.room_relation_delete.message.package')
            ],
            User::class => [
                new Relationship(Task::class, 'performer', 'exception.user_relation_delete.message.task'),
                new Relationship(WorkShift::class, 'closedBy', 'exception.user_relation_delete.message.workShift')
            ],
            Group::class => [
                new Relationship(TaskType::class, 'defaultUserGroup', 'exception.group_relation_delete.message.taskType'),
                new Relationship(User::class, 'groups', 'exception.group_relation_delete.message.user', true)
            ],
            Order::class => [
                //new Relationship(Package::class, 'order', 'exception.order_relation_delete.message.package'),
                //new Relationship(CashDocument::class, 'order', 'exception.order_relation_delete.message.cashDocument')
            ],
            Tariff::class => [
                //new Relationship(PriceCache::class, 'tariff', 'exception.tariff_relation_delete.message.priceCache'),
                //new Relationship(Restriction::class, 'tariff', 'exception.tariff_relation_delete.message.restriction'),
                //new Relationship(RoomCache::class, 'tariff', 'exception.tariff_relation_delete.message.roomCache'),
                new Relationship(Tariff::class, 'parent', 'exception.tariff_relation_delete.message.parentTariff'),
                new Relationship(Package::class, 'tariff', 'exception.tariff_relation_delete.message.package')
            ],
            ServiceCategory::class => [
                new Relationship(Service::class, 'category', 'exception.serviceCategory_relation_message.service')
            ],
            Promotion::class => [
                new Relationship(Tariff::class, 'defaultPromotion', 'exception.promotion_relation_delete.message.tariff'),
                new Relationship(Package::class, 'promotion', 'exception.promotion_relation_delete.message.package')
            ],
            DishMenuCategory::class => [
                new Relationship(DishMenuItem::class, 'category', 'exception.dishMenuCategory_relation_delete.message.dishMenuItem'),
            ],
            Table::class => [
                new Relationship(DishOrderItem::class, 'table', 'exception.table_relation_delete.message.dishorderItem')
            ],
            Package::class => [
                //new Relationship(DishOrderItem::class, 'order', 'exception.package_relation_delete.message.dishOrderItem'),
                //new Relationship(PackageService::class, 'package', 'exception.package_relation_delete.message.packageService')
            ],
            IngredientCategory::class => [
                new Relationship(Ingredient::class, 'category', 'exception.ingredientCategory_relation_delete.message.ingredient')
            ],
            WareItem::class => [
                new Relationship(Record::class, 'wareItem', 'exception.wareItem_relation_delete.message.record')
            ],
            WareCategory::class => [
                new Relationship(WareItem::class, 'category', 'exception.wareCategory_relation_delete.message.wareItem')
            ],
            PackageSource::class => [
                new Relationship(Order::class, 'source', 'exception.packageSource_relation_delete.message.order')
            ],
            Tourist::class => [
                new Relationship(Order::class, 'mainTourist', 'exception.tourist_relation_delete.message.order'),
                new Relationship(CashDocument::class, 'touristPayer', 'exception.tourist_relation_delete.message.cashDocument'),
                new Relationship(Package::class, 'tourists', 'exception.tourist_relation_delete.message.package', true)
            ],
            Country::class => [
                new Relationship(Organization::class, 'country', 'exception.country_relation_delete.message.organization')
            ],
            Region::class => [
                new Relationship(Organization::class, 'region', 'exception.region_relation_delete.message.organization')
            ],
            Service::class => [
                new Relationship(PackageService::class, 'service', 'exception.service_relation_delete.message.packageService'),
                new Relationship(Tariff::class, 'services', 'exception.service_relation_delete.message.tariff', true)
            ],
            VegaState::class => [
                new Relationship(Tourist::class, 'citizenship', 'exception.vegaState_relation_delete.message.tourist'),
                new Relationship(Tourist::class, 'birthplace.country', 'exception.vegaState_relation_delete.message.touristBirthplace')
            ],
            Housing::class => [
                new Relationship(Room::class, 'housing', 'exception.housing_relation_delete.message.room')
            ],
            Special::class => [
                new Relationship(Package::class, 'special', 'exception.special_relation_delete.message.package')
            ]
        ];
    }
}