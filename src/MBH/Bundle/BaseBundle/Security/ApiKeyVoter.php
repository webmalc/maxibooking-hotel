<?php


namespace MBH\Bundle\BaseBundle\Security;


use MBH\Bundle\UserBundle\Security\ApiKeyAuthenticator;
use MBH\Bundle\UserBundle\Security\ApiKeyUserProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ApiKeyVoter extends Voter
{

    private const RESTRICTION_ROLE_NAMES = [
        'ROLE_USER', 'ROLE_GROUP', 'ROLE_PROFILE'
    ];

    /** @var RoleHierarchyInterface */
    private $roleHierarchy;

    /** @var array */
    private $restrictRoles;

    /**
     * ApiKeyVoter constructor.
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
        $this->initRestrictionRoles();
    }


    protected function supports($attribute, $subject)
    {
        return in_array($attribute, $this->restrictRoles, true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $roles = [];
        foreach ($token->getRoles() as $role) {
            if ($role instanceof Role) {
                $roles[] = $role->getRole();
            }
        }

        return !in_array(ApiKeyAuthenticator::ROLE_ACCESS_WITH_TOKEN, $roles, true);
    }

    private function initRestrictionRoles()
    {
        $roles = [];
        foreach (static::RESTRICTION_ROLE_NAMES as $roleName) {
            $roles[] = new Role($roleName);
        }
        foreach ($this->roleHierarchy->getReachableRoles($roles) as $restrictRole) {
            $this->restrictRoles[] = $restrictRole->getRole();
        }
    }


}