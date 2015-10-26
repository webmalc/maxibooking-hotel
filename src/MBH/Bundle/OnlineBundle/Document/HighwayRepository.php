<?php

namespace MBH\Bundle\OnlineBundle\Document;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * Class HighwayRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class HighwayRepository
{
    /**
     * @var FileLocatorInterface
     */
    protected $fileLocator;

    protected $list = [];

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
            $path = $this->fileLocator->locate('@MBHOnlineBundle/Resources/fixture/highway.txt');
            $this->list = explode("\n", file_get_contents($path));
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
}