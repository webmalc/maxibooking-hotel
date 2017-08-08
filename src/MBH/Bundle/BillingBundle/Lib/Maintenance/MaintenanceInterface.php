<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


interface MaintenanceInterface
{
    public function install(string $clientName);

    public function rollBack(string $clientName);

    public function remove(string $clientName);

    public function restore(string $clientName);

    public function update(string $clientName, string $serverIp = null);

}