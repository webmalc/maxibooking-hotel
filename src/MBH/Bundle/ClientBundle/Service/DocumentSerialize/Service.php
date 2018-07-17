<?php
/**
 * Created by PhpStorm.
 * Date: 16.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\PackageBundle\Document\PackageService;

/**
 * Class Service
 *
 * @property PackageService $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class Service extends Common
{
    protected const METHOD = [
        'getAmount',
        'getTotal|money',
        'getPrice|money'
    ];

    public function getServiceName(): string
    {
        return $this->entity->getService() ?? '';
    }

    protected function getSourceClassName()
    {
        return PackageService::class;
    }
}