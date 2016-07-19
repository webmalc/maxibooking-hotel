<?php

namespace MongoDBODMProxies\__CG__\MBH\Bundle\RestaurantBundle\Document;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class DishMenuItem extends \MBH\Bundle\RestaurantBundle\Document\DishMenuItem implements \Doctrine\ODM\MongoDB\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = [];



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', 'category', 'fullTitle', 'title', 'price', 'margin', 'isMargin', 'description', 'dishIngredients', 'id', 'isEnabled', 'createdAt', 'updatedAt', 'deletedAt', 'createdBy', 'updatedBy'];
        }

        return ['__isInitialized__', 'category', 'fullTitle', 'title', 'price', 'margin', 'isMargin', 'description', 'dishIngredients', 'id', 'isEnabled', 'createdAt', 'updatedAt', 'deletedAt', 'createdBy', 'updatedBy'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (DishMenuItem $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);

        parent::__clone();
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getCategory(): \MBH\Bundle\RestaurantBundle\Document\DishMenuCategory
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCategory', []);

        return parent::getCategory();
    }

    /**
     * {@inheritDoc}
     */
    public function setCategory(\MBH\Bundle\RestaurantBundle\Document\DishMenuCategory $category)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCategory', [$category]);

        return parent::setCategory($category);
    }

    /**
     * {@inheritDoc}
     */
    public function getFullTitle(): string
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFullTitle', []);

        return parent::getFullTitle();
    }

    /**
     * {@inheritDoc}
     */
    public function setFullTitle($fullTitle)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFullTitle', [$fullTitle]);

        return parent::setFullTitle($fullTitle);
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTitle', []);

        return parent::getTitle();
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($title)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTitle', [$title]);

        return parent::setTitle($title);
    }

    /**
     * {@inheritDoc}
     */
    public function getPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPrice', []);

        return parent::getPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function setPrice($price)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPrice', [$price]);

        return parent::setPrice($price);
    }

    /**
     * {@inheritDoc}
     */
    public function getCostPrice(): float
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCostPrice', []);

        return parent::getCostPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function setCostPrice($costPrice = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCostPrice', [$costPrice]);

        return parent::setCostPrice($costPrice);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', [$description]);

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getHotel()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getHotel', []);

        return parent::getHotel();
    }

    /**
     * {@inheritDoc}
     */
    public function getDishIngredients()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDishIngredients', []);

        return parent::getDishIngredients();
    }

    /**
     * {@inheritDoc}
     */
    public function setDishIngredients(\MBH\Bundle\RestaurantBundle\Form\DishMenuIngredientEmbeddedType $dishIngredients)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDishIngredients', [$dishIngredients]);

        return parent::setDishIngredients($dishIngredients);
    }

    /**
     * {@inheritDoc}
     */
    public function addDishIngredients(\MBH\Bundle\RestaurantBundle\Document\DishMenuIngredientEmbedded $ingredient)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addDishIngredients', [$ingredient]);

        return parent::addDishIngredients($ingredient);
    }

    /**
     * {@inheritDoc}
     */
    public function getMargin()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMargin', []);

        return parent::getMargin();
    }

    /**
     * {@inheritDoc}
     */
    public function setMargin($margin)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMargin', [$margin]);

        return parent::setMargin($margin);
    }

    /**
     * {@inheritDoc}
     */
    public function getIsMargin()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsMargin', []);

        return parent::getIsMargin();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsMargin($isMargin)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsMargin', [$isMargin]);

        return parent::setIsMargin($isMargin);
    }

    /**
     * {@inheritDoc}
     */
    public function getMarginPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMarginPrice', []);

        return parent::getMarginPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function getActualPrice()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActualPrice', []);

        return parent::getActualPrice();
    }

    /**
     * {@inheritDoc}
     */
    public function getIsEnabled()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getIsEnabled', []);

        return parent::getIsEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function setIsEnabled($isEnabled)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setIsEnabled', [$isEnabled]);

        return parent::setIsEnabled($isEnabled);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', []);

        return parent::__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', []);

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedAt', [$createdAt]);

        return parent::setCreatedAt($createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedAt', []);

        return parent::getCreatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedAt', [$updatedAt]);

        return parent::setUpdatedAt($updatedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedAt', []);

        return parent::getUpdatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function setDeletedAt(\DateTime $deletedAt = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDeletedAt', [$deletedAt]);

        return parent::setDeletedAt($deletedAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getDeletedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeletedAt', []);

        return parent::getDeletedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function isDeleted()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isDeleted', []);

        return parent::isDeleted();
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedBy($createdBy)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedBy', [$createdBy]);

        return parent::setCreatedBy($createdBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedBy', []);

        return parent::getCreatedBy();
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedBy($updatedBy)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedBy', [$updatedBy]);

        return parent::setUpdatedBy($updatedBy);
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedBy', []);

        return parent::getUpdatedBy();
    }

}
