<?php


namespace MBH\Bundle\PackageBundle\Document;

/**
 * Class UnwelcomeHistory

 */
class UnwelcomeHistory
{
    /**
     * @var Tourist
     */
    protected $tourist;

    /**
     * @var Unwelcome[]
     */
    protected $items = [];

    /**
     * @return Unwelcome[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Unwelcome[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @param Unwelcome $item
     */
    public function addItem(Unwelcome $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return Tourist
     */
    public function getTourist()
    {
        return $this->tourist;
    }

    /**
     * @param Tourist $tourist
     */
    public function setTourist(Tourist $tourist = null)
    {
        $this->tourist = $tourist;
    }
}