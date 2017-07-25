<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Model\Client;

interface MaintenanceInterface
{
    public function install(Client $client);

    public function rollBack(Client $client);

    public function remove(Client $client);

    public function restore(Client $client);

    public function update(Client $client);

}