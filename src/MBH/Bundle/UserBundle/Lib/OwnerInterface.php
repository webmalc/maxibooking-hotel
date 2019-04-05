<?php


namespace MBH\Bundle\UserBundle\Lib;


use MBH\Bundle\UserBundle\Document\User;

interface OwnerInterface
{
    public function getOwner(): ?User;

    public function setOwner(User $user): self;
}