<?php

namespace MBH\Bundle\PackageBundle\Lib;


use Dinhkhanh\MongoDBAclBundle\Security\Domain\MutableAclProvider;
use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Blameable\Traits\Blameable;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Class AclOwnerMaker
 * @package MBH\Bundle\PackageBundle\Lib
 */
class AclOwnerMaker
{
    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * PackageOwnerMaker constructor.
     * @param MutableAclProvider $aclProvider
     */
    public function __construct(MutableAclProvider $aclProvider, DocumentManager $dm)
    {
        $this->aclProvider = $aclProvider;
        $this->dm = $dm;
    }


    /**
     * @param User $user
     * @param Base $document
     */
    public function insertAcl(User $user, Base $document)

    {
        if (!$user->isSuperAdmin()) {
            $aclProvider = $this->aclProvider;
            try {
                $acl = $aclProvider->createAcl(ObjectIdentity::fromDomainObject($document));
                $acl->insertObjectAce(UserSecurityIdentity::fromAccount($user), MaskBuilder::MASK_MASTER);
                $aclProvider->updateAcl($acl);
            } catch (AclAlreadyExistsException $exception) {

            }

        }
    }

    /**
     * Assign ownership of the document and add it to ACL
     *
     * @param User $user
     * @param Base $document
     * @param bool $isSetAcl
     */
    public function assignOwnerToDocument(User $user, Base $document, $isSetAcl = true)
    {
        /** @var \Gedmo\Blameable\Traits\Blameable $document*/
        if (method_exists($document, 'getCreatedBy') && !$document->getCreatedBy()) {
            $document->setCreatedBy($user->getUsername());
            $this->dm->persist($document);
            $this->dm->flush();
            if ($isSetAcl) {
                $this->insertAcl($user, $document);
            }
        }

    }
}
