<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Model\string;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NginxMaintenance extends AbstractMaintenance
{

    public function install(string $clientName)
    {
        $twig = $this->getContainer()->get('templating');
        try {
            $nginxConfig = $twig->render(
                '@MBHBilling/Maintenance/nginx.config.html.twig',
                [
                    'client' => $clientName,
                    'codeFolder' => $this->options['codeFolder'],
                ]
            );
        } catch (\Twig_Error_Runtime $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }


        $this->installNginxFiles($clientName, $nginxConfig);
    }

    public function rollBack(string $clientName)
    {
        $this->remove($clientName);
    }

    public function remove(string $clientName)
    {
        $this->removeFile($this->getNginxLinkName($clientName));
        $this->removeFile($this->getNginxFileName($clientName));
    }

    public function restore(string $clientName)
    {
        $this->install($clientName);
    }

    public function update(string $clientName)
    {
    }

    private function installNginxFiles(string $client, string $nginxConfig = '')
    {
        $this->createFile($client, $nginxConfig);
        $this->createLink($client);
    }

    private function getNginxFileName(string $clientName): string
    {
        return $this->options['sitesAvailable'].'/'.$clientName;
    }

    private function getNginxLinkName(string $clientName): string
    {
        return $this->options['sitesEnabled'].'/'.$clientName;
    }

    private function createFile(string $clientName, string $file)
    {
        $this->dumpFile($this->getNginxFileName($clientName), $file);
    }

    private function createLink(string $clientName)
    {
        $this->createSymLink($this->getNginxFileName($clientName), $this->getNginxLinkName($clientName));
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setRequired('codeFolder')
            ->setDefaults(
                [
                    'codeFolder' => 'maxibooking',
                    'sitesAvailable' => '/etc/nginx/sites-available',
                    'sitesEnabled' => '/etc/nginx/sites-enabled',
                ]
            );
    }



}