<?php
/**
 * Created by PhpStorm.
 * Date: 28.06.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Menu;


class Badge
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $value;

    /**
     * Badge constructor.
     * @param string $id
     * @param string $class
     * @param string $title
     * @param string $value
     */
    public function __construct(string $id, string $class, string $title, string $value)
    {
        $this->id = $id;
        $this->class = 'badge-sidebar ' . $class;
        $this->title = $title;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}