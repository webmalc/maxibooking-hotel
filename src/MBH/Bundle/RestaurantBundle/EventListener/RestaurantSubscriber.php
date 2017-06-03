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
use MBH\Bundle\RestaurantBundle\Document\DishMenuCategory;
use MBH\Bundle\RestaurantBundle\Document\DishMenuItem;
use MBH\Bundle\RestaurantBundle\Document\Ingredient;
use MBH\Bundle\RestaurantBundle\Document\IngredientCategory;
use MBH\Bundle\RestaurantBundle\Document\Table;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
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
                throw new DeleteException('exception.ingredient_relation_delete.message.dishIngredient', $query->count());
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

                $message = $this->container->get('translator')->trans('exception.ingredient_relation_delete.message.dish', ['%hrefTagStart%' => '<a href="'.$route.'">']);
                throw new DeleteException($message . '</a>');
            }
        }
    }

}