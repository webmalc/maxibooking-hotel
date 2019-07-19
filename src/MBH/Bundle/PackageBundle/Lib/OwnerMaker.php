<?php

namespace MBH\Bundle\PackageBundle\Lib;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Base;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Lib\OwnerInterface;

/**
 * Class AclOwnerMaker
 * @package MBH\Bundle\PackageBundle\Lib
 */
class OwnerMaker
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * PackageOwnerMaker constructor.
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }



    /**
     * Assign ownership of the document and add it to ACL
     *
     * @param User $user
     * @param Base $document
     * @param bool $isSetAcl
     */
    public function assignOwnerToDocument(User $user, OwnerInterface $document)
    {
        $document->setOwner($user);
        /** @var BlameableDocument $document */
        $document->setCreatedBy($user);
        $this->dm->persist($document);
        $this->dm->flush($document);
    }
}
