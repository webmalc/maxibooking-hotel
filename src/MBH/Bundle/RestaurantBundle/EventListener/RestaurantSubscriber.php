<?php
/**
 * Created by PhpStorm.
 * User: zalex
 * Date: 01.07.16
 * Time: 15:44
 */

namespace MBH\Bundle\RestaurantBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItem;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Document\Table;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RestaurantSubscriber
 * @package MBH\Bundle\RestaurantBundle\EventListener
 */
class RestaurantSubscriber implements EventSubscriber
{

    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * RestaurantSubscriber constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preRemove'
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DeleteException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $doc = $args->getDocument();

        if ($doc instanceof Ingredient) {
            $query = $args->getDocumentManager()->getRepository('MBHRestaurantBundle:DishMenuItem')
                ->createQueryBuilder()
                ->field('dishIngredients.ingredient.$id')->equals(new \MongoId($doc->getId()))
                ->getQuery();

            if ($query->count()) {
                $dishlist = '';
                $router = $this->container->get('router');

                foreach ($query->execute() as $dishItem) {
                    /** @var DishMenuItem $dishItem */
                    $route = $router->generate('restaurant_dishmenu_item_edit', ['id' => $dishItem->getId()]);
                    $dishlist .= '<a href="' . $route . '">' . $dishItem->getName() . '</a> .';
                }

                $message = 'Невозможно удалить ингредиент ' . $doc->getName() . ' так как он используется в блюдах: ' . $dishlist;
                throw new DeleteException($message);
            }
        }

        if ($doc instanceof Table) {
            $query = $args->getDocumentManager()->getRepository('MBHRestaurantBundle:DishOrderItem')
                ->createQueryBuilder()
                ->field('table.$id')->equals(new \MongoId($doc->getId()))
                ->getQuery();

            if ($query->count()) {
                $router = $this->container->get('router');
                $route = $router->generate('restaurant_table_edit', ['id' => $doc->getId()]);
                $message = 'Невозможно удалить столик. Вы всегда можете его скрыть в <a href="'.$route.'"> меню редактирования'.'</a>';
                throw new DeleteException($message);
            }
        }

        if ($doc instanceof DishMenuItem) {
            $query = $args->getDocumentManager()->getRepository('MBHRestaurantBundle:DishOrderItem')
                ->createQueryBuilder()
                ->field('dishes.dishMenuItem.$id')->equals(new \MongoId($doc->getId()))
                ->getQuery();

            if ($query->count()) {
                $router = $this->container->get('router');
                $route = $router->generate('restaurant_dishmenu_item_edit', ['id' => $doc->getId()]);
                $message = 'Невозможно удалить блюдо, т.к. оно фигурирует в заказах. Вы всегда можете его скрыть в <a href="'.$route.'"> меню редактирования'.'</a>';
                throw new DeleteException($message);
            }
        }
    }

}