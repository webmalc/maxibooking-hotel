<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use MBH\Bundle\BillingBundle\Lib\Exceptions\ClientMaintenanceException;
use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class NginxMaintenance extends AbstractMaintenance
{

    public function install(Client $client)
    {
        $twig = $this->getContainer()->get('templating');
        try {
            $nginxConfig = $twig->render(
                '@MBHBilling/Maintenance/nginx.config.html.twig',
                [
                    'client' => $client->getName(),
                    'codeFolder' => $this->options['codeFolder'],
                ]
            );
        } catch (\Twig_Error_Runtime $e) {
            throw new ClientMaintenanceException($e->getMessage());
        }


        $this->installNginxFiles($client->getName(), $nginxConfig);
    }

    public function rollBack(Client $client)
    {
        $this->remove($client);
    }

    public function remove(Client $client)
    {
        $this->removeFile($this->getNginxLinkName($client->getName()));
        $this->removeFile($this->getNginxFileName($client->getName()));
    }

    public function restore(Client $client)
    {
        $this->install($client);
    }

    public function update(Client $client)
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