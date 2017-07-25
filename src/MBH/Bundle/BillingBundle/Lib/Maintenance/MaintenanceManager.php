<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use Doctrine\Common\Collections\ArrayCollection;
use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\BillingBundle\Lib\Model\Client;

class MaintenanceManager
{
    /** @var  MaintenanceInterface[] */
    private $maintenances;

    /** @var PostMaintenanceInterface[] */
    private $postMaintenance;

    public function __construct()
    {
        $this->maintenances = new ArrayCollection();
        $this->postMaintenance = new ArrayCollection();
    }


    public function addMaintenance(MaintenanceInterface $maintenance)
    {
        $this->maintenances->add($maintenance);
    }

    public function addPostMaintenance(PostMaintenanceInterface $postMaintenance)
    {
        $this->postMaintenance->add($postMaintenance);
    }

    private function getMaintenances()
    {
        if (!$this->maintenances) {
            throw new Exception('No installers in installManager');
        }

        return $this->maintenances;
    }

    public function install(Client $client)
    {

        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->install($client);
        }
    }


    public function rollBack(Client $client)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->rollBack($client);
        }
    }

    public function remove(Client $client)
    {
        foreach ($this->getMaintenances() as $maintenance) {
            $maintenance->remove($client);
        }
    }

    public function afterInstall(Client $client)
    {
        foreach ($this->postMaintenance as $postMaintenance) {
            $postMaintenance->afterInstall($client);
        }
    }


}