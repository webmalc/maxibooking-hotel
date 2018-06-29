<?php
/**
 * Created by PhpStorm.
 * Date: 28.06.18
 */

namespace MBH\Bundle\BaseBundle\Lib\Menu;


class BadgesHolder
{
    /**
     * @var Badge[]
     */
    private $badges = [];

    public function addBadgeObj(Badge $badge): void
    {
        $this->badges[] = $badge;
    }

    public function addBadge(string $id, string $class, string $title, string $value): void
    {
        $this->badges[] = new Badge($id, $class, $title, $value);
    }

    /**
     * @return array
     */
    public function addInAttributes(): array
    {
        if ($this->badges === []) {
            return [];
        }

        return [
            'badges' => $this->badges
        ];
    }

    /**
     * @param string $id
     * @param string $class
     * @param string $title
     * @param string $value
     * @return array
     */
    public static function createOne(string $id, string $class, string $title, string $value): array
    {
        $b = new self();
        $b->addBadge($id, $class, $title, $value);

        return $b->addInAttributes();
    }
}