<?php


namespace MBH\Bundle\UserBundle\Lib;


use MBH\Bundle\UserBundle\Document\User;

trait OwnerTrait
{

    /**
     * @var User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $owner;

    /**
     * @return User
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setOwner(User $user): OwnerInterface
    {
        $this->owner = $user;

        return $this;
    }
}