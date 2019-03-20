<?php


namespace MBH\Bundle\BaseBundle\Security;


use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Lib\OwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PackageVoter extends Voter
{
    public const SUPPORTS = [
        'edit', 'view'
    ];
    protected function supports($attribute, $subject)
    {
        return in_array(mb_strtolower($attribute), self::SUPPORTS, true) && $subject instanceof OwnerInterface;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        $userId = $user->getId();
        /** @var User $owner */
        /** @var  OwnerInterface $subject */
        $owner = $subject->getOwner();
        if ($owner) {
            return $owner->getId() === $userId;
        }
        /** @var Package $subject */
        return $subject->getCreatedBy() === $user->getName()
            || $subject->getCreatedBy() === null;
    }

}