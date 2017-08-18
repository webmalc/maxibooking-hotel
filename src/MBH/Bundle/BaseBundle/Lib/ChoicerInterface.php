<?php


namespace MBH\Bundle\BaseBundle\Lib;


interface ChoicerInterface
{
    public function getReceiverGroup(): ?string;

    public function getMessageType(): ?string;
}