<?php


namespace MBH\Bundle\BaseBundle\Security;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Lib\HotelAccessibleInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class HotelVoter
 * @package MBH\Bundle\BaseBundle\Security
 */
class HotelVoter extends Voter
{

    /**
     *
     */
    public const ACCESS = 'access';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Hotel && self::ACCESS === $attribute;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var HotelAccessibleInterface $user */
        $user = $token->getUser();
        $hotels = $user->getAccessibleHotels();
        if (\is_iterable($hotels)) {
            foreach ($hotels as $hotel) {
                if ($subject->getId() === $hotel->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

}