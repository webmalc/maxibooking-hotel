<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


use MBH\Bundle\BaseBundle\Lib\Exception;

class InstallerManager
{
    /** @var  InstallerInterface[] */
    private $installers = [];

    public function addInstaller(InstallerInterface $installer)
    {
        $this->installers[] = $installer;
    }

    public function install(string $client)
    {
        if (!$this->installers) {
            throw new Exception('No installers in installManager');
        }
        foreach ($this->installers as $installer) {
            $installer->install($client);
        }
    }

    public function rollBack(string $client)
    {
        foreach ($this->installers as $installer) {
            $installer->rollBack($client);
        }
    }

    public function remove(string $client)
    {
        foreach ($this->installers as $installer) {
            $installer->remove($client);
        }
    }

}