<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractPackageInfo
{
    /** @var  ContainerInterface $container */
    protected $container;
    /** @var  DocumentManager $dm */
    protected $dm;
    protected $note = '';
    protected $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->translator = $container->get('translator');
    }

    abstract public function getBeginDate();
    abstract public function getEndDate();
    abstract public function getRoomType();
    abstract public function getTariff();
    abstract public function getAdultsCount();
    abstract public function getChildrenCount();
    abstract public function getPrices();
    abstract public function getPrice();
    abstract public function getNote();
    abstract public function getIsCorrupted();
    abstract public function getTourists();
    abstract public function getIsSmoking();
    abstract public function getChannelManagerId();

    public function getOriginalPrice()
    {
        return $this->getPrice();
    }

    protected function addPackageNote($note, $preface = null) : string
    {
        $note = trim($note);
        if ($note !== '') {
            if ($preface) {
                $note = $this->container->get('translator')->trans($preface) . ': ' . $note;
            }
            $this->note .= $note . "\n";
        }

        return $this->note;
    }
}