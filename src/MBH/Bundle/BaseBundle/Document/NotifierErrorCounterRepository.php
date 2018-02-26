<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class NotifierErrorCounterRepository extends DocumentRepository
{
    public function removeErrorCounterIfExists(string $notificationId)
    {
        $errorCounter = $this->findOneBy(['notificationId' => $notificationId]);
        if (!is_null($errorCounter)) {
            $this->dm->remove($errorCounter);
            $this->dm->flush();
        }
    }
}