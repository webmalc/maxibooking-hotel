<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use MBH\Bundle\BaseBundle\Lib\BaseRepositoryInterface;
use MBH\Bundle\BaseBundle\Lib\QueryBuilder;

abstract class AbstractBaseRepository extends DocumentRepository implements BaseRepositoryInterface
{
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->dm, $this->documentName);
    }
}
