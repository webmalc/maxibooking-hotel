<?php
/**
 * Created by PhpStorm.
 * Date: 15.05.18
 */

namespace MBH\Bundle\ClientBundle\Service\DocumentSerialize;

use MBH\Bundle\PackageBundle\Component\PackageServiceGroupByService;

/**
 * Class ServiceGroup
 *
 * @property PackageServiceGroupByService $entity
 *
 * @package MBH\Bundle\ClientBundle\Service\DocumentSerialize
 */
class ServiceGroup extends Common
{
    protected const METHOD = [
        'getTotal|money',
        'getPrice|money',
        'getActuallyAmount',
    ];

    protected const EXCLUDED_METHOD = [
        'getByService',
    ];

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->entity->getByService() ?? $this->entity->getByService()->getName() ?? '';
    }

    /**
     * @param $packageService
     * @throws \Exception
     */
    public function add($packageService): void
    {
        $this->entity->add($packageService);
    }

    protected function getSourceClassName()
    {
        return PackageServiceGroupByService::class;
    }
}