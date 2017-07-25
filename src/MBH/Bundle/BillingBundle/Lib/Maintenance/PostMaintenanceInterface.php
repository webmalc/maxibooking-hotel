<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Model\Client;

interface PostMaintenanceInterface
{
    public function afterInstall(Client $client);
}