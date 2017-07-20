<?php


namespace MBH\Bundle\BillingBundle\Lib\Maintenance;


use Symfony\Component\OptionsResolver\OptionsResolver;

final class NginxMaintenance extends AbstractMaintenance
{

    public function install(string $client)
    {
        $twig = $this->getContainer()->get('templating');
        $nginxConfig = $twig->render(
            '@MBHBilling/Maintenance/nginx.config.html.twig',
            [
                'client' => $client,
                'codeFolder' => $this->options['codeFolder'],
            ]
        );

        $this->installNginxFiles($client, $nginxConfig);
    }

    public function rollBack(string $client)
    {
        $this->remove($client);
    }

    public function remove(string $client)
    {
        $this->removeFile($this->getNginxLinkName($client));
        $this->removeFile($this->getNginxFileName($client));
    }

    public function restore(string $client)
    {
        $this->install($client);
    }

    public function update(string $client)
    {
    }

    private function installNginxFiles(string $client, string $nginxConfig = '')
    {
        $this->createFile($client, $nginxConfig);
        $this->createLink($client);
    }

    private function getNginxFileName(string $client): string
    {
        return $this->options['sitesAvailable'].'/'.$client;
    }

    private function getNginxLinkName(string $client): string
    {
        return $this->options['sitesEnabled'].'/'.$client;
    }

    private function createFile(string $client, string $file)
    {
        $this->dumpFile($this->getNginxFileName($client), $file);
    }

    private function createLink(string $client)
    {
        $this->createSymLink($this->getNginxFileName($client), $this->getNginxLinkName($client));
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