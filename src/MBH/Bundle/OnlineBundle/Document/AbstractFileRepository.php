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
     * @param $regexQuery
     * @return array
     */
    public function search($regexQuery)
    {
        $list = $this->getList();
        return array_filter($list, function($highway) use ($regexQuery) {
            return preg_match($regexQuery, $highway) > 0;
        });
    }

    abstract public function getFilePath();
}