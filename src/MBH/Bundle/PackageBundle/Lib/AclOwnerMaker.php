<?php

namespace MBH\Bundle\PackageBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
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

    private $aclProvider;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * PackageOwnerMaker constructor.
     */
    public function __construct(bool $aclProvider, DocumentManager $dm)
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
        throw new \RuntimeException('Create Voters!');
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
        throw new \RuntimeException('Create Voters!');

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
