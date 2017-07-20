<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


interface MaintenanceInterface
{
    public function install(string $client);

    public function rollBack(string $client);

    public function remove(string $client);

    public function restore(string $client);

    public function update(string $client);

}