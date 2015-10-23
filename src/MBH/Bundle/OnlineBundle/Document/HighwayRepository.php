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

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * @return array
     */
    public function getList()
    {
        $path = $this->fileLocator->locate('@MBHOnlineBundle/Resources/fixture/highway.txt');
        $highwayList = explode("\n", file_get_contents($path));

        return $highwayList;
    }
}