<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\HotelBundle\Document\RoomType;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractPackageInfo
{
    /** @var  ContainerInterface $container */
    protected $container;
    /** @var  DocumentManager $dm */
    protected $dm;
    protected $note = '';
    protected $translator;
    protected $noteMessages = [];
    protected $isCorrupted = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->translator = $container->get('translator');
    }

    abstract public function getBeginDate();
    abstract public function getEndDate();
    abstract public function getRoomType() : RoomType;
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

    protected function addProblemMessage($transIdentifier, $params = [])
    {
        return $this->addMessage('problems', $transIdentifier, $params);
    }

    protected function addNotifyMessage($transIdentifier, $params = [])
    {
        return $this->addMessage('notifications', $transIdentifier, $params);
    }

    private function addMessage($status, $transIdentifier, $params)
    {
        $this->noteMessages[$status] = [
            'identifier' => $transIdentifier,
            'params' => $params
        ];

        return $this;
    }

    public function getMessages() : array
    {
        return $this->noteMessages;
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

    public function getChildAges() {
        return [];
    }
}