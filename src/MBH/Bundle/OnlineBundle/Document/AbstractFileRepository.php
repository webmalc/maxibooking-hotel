<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * Class AbstractFileRepository
 * @package MBH\Bundle\OnlineBundle\Document
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
abstract class AbstractFileRepository
{
    protected $list = [];

    /**
     * @var FileLocatorInterface
     */
    protected $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * @return array
     */
    public function getList()
    {
        if(!$this->list) {
            $this->list = explode("\n", file_get_contents($this->getFilePath()));
        }

        return $this->list;
    }

    /**
     * @param $query
     * @return array
     */
    public function search($query)
    {
        $list = $this->getList();
        $query = mb_strtolower($query);
        return array_filter($list, function($item) use ($query) {
            return mb_strpos(mb_strtolower($item), $query) !== false;
        });
    }

    abstract public function getFilePath();
}