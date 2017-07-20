<?php


namespace MBH\Bundle\BillingBundle\Lib\Installer;


class NginxInstaller extends AbstractInstaller
{

    const DEFAULT_CODE_FOLDER = 'maxibooking';

    public function install(string $client)
    {
        $twig = $this->getContainer()->get('templating');
        $nginxConfig = $twig->render(
            '@MBHBillin/Installer/nginx.config.html.twig',
            [
                'client' => $client,
                'codeFolder' => self::DEFAULT_CODE_FOLDER
            ]
        );

        $this->dumpFile('/etc/nginx/sites-available/'.$client, $nginxConfig);
    }

    public function rollBack(string $client)
    {
        $this->removeFile('/etc/nginx/sites-available/'.$client);
    }


}