<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


interface InstallerInterface
{
    public function install(string $client);

    public function rollBack(string $client);

//    public function remove(string $client);

}