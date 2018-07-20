<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 26.06.17
 * Time: 16:07
 */

namespace MBH\Bundle\ClientBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class ColorsConfigRepository extends DocumentRepository
{
    public function fetchConfig()
    {
        $qb = $this->createQueryBuilder();
        $config = $qb->getQuery()->getSingleResult();

        if (!$config) {
            $config = new ColorsConfig();
            $this->dm->persist($config);
            $this->dm->flush();
        }

        return $config;
    }
}