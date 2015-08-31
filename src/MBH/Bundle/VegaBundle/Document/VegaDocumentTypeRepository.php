<?php

namespace MBH\Bundle\VegaBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class VegaDocumentTypeRepository
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 *
 * @method null|VegaDocumentType getByCode(string $code)
 */
class VegaDocumentTypeRepository extends DocumentRepository
{
}