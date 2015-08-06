<?php

namespace MBH\Bundle\HotelBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class TaskTypeCategoryRepository
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 *
 * @method null|TaskTypeCategory findOneByCode(string $code)
 */
class TaskTypeCategoryRepository extends DocumentRepository
{

}