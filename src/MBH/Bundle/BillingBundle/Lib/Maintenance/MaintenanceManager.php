<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BaseBundle\Lib\Exception;

class MaintenanceManager
{
    /** @var  MaintenanceInterface[] */
    private $maintenances = [];

    public function addMaintenance(MaintenanceInterface $maintenance)
    {
        $this->maintenances[] = $maintenance;
    }

    private function getMaintenances()
    {
        if (!$this->maintenances) {
            throw new Exception('No installers in installManager');
        }

        return $this->maintenances;
    }

    public function install(string $client)
    {

        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->install($client);
        }
    }

    public function rollBack(string $client)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->rollBack($client);
        }
    }

    public function remove(string $client)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->remove($client);
        }
    }

}