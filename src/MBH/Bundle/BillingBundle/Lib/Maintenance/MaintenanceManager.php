<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Lib\Exception;

class MaintenanceManager
{
    /** @var  MaintenanceInterface[] */
    private $maintenances;

public function __construct()
    {
        $this->maintenances = new ArrayCollection();
    }


    public function addMaintenance(MaintenanceInterface $maintenance)
    {
        $this->maintenances->add($maintenance);
    }

    private function getMaintenances()
    {
        if (!$this->maintenances) {
            throw new Exception('No installers in installManager');
        }

        return $this->maintenances;
    }

    public function install(string $clientName)
    {

        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->install($clientName);
        }
    }


    public function rollBack(string $clientName)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->rollBack($clientName);
        }
    }

    public function remove(string $clientName)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->remove($clientName);
        }
    }

}