<?php


namespace MBH\Bundle\BaseBundle\Service\AclMigrate;


use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Document\Traits\BlameableDocument;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\UserBundle\Document\User;
use MBH\Bundle\UserBundle\Lib\OwnerInterface;

class AclMigratory
{
    /** @var DocumentManager */
    private $dm;

    /**
     * AclMigratory constructor.
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function doHotelMigrate(): int
    {

        $migrate = function (Hotel $hotel, User $user) {
            $user->addHotel($hotel);
            $this->dm->flush($user);
        };

        return $this->createMigrate(Hotel::class, $migrate);
    }

    public function doPackageMigrate(): int
    {
        $migrate = function (OwnerInterface $document, User $user) {
            /** @var BlameableDocument|OwnerInterface $document */
            $document->setOwner($user);
            $document->setCreatedBy($user);
            $this->dm->flush($document);
        };

        $migrated = 0;
        $migrated += $this->createMigrate(Package::class, $migrate);
        $migrated += $this->createMigrate(Order::class, $migrate);

        return $migrated;
    }

    private function createMigrate(string $documentName, callable $migrate)
    {
        $migrated = 0;
        $dbName = $this->dm->getConfiguration()->getDefaultDB();
        $entryCollection = $this->dm->getConnection()->selectCollection($dbName, 'acl_entry');
        $oidCollection = $this->dm->getConnection()->selectCollection($dbName, 'acl_oid');
        $oidMap = [];



        $oids = $oidCollection->find(['type' => $documentName]);
        if ($oids->count(true)) {
            foreach ($oids as $oid) {
                $oidsIds[] = $oid['_id'];
                $oidMap[(string)$oid['_id']] = $oid;
            }
        }

        $entries = $entryCollection->find(['objectIdentity.$id' => ['$in' => $oidsIds]]);
        $documentRepository = $this->dm->getRepository($documentName);
        if ($entries->count(true)) {
            foreach ($entries as $entry) {
                $userName = $entry['securityIdentity']['username'];
                $user = $this->dm->getRepository(User::class)->findOneBy(['username' => $userName]);
                if ($user) {
                    $currentOid = $oidMap[(string)$entry['objectIdentity']['$id']];
                    $document = $documentRepository->find($currentOid['identifier']);
                    if ($document) {
                        $migrate($document, $user);
                        $migrated++;
                    }

                }
            }
        }

        return $migrated;
    }
}