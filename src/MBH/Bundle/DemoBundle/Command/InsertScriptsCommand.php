<?php

namespace MBH\Bundle\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InsertScriptsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mbh:demo:insert_scripts')
            ->setDescription('Insert scripts in template')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $this->getContainer()->get('kernel')->getRootDir();
        $path .= '/../src/MBH/Bundle/BaseBundle/Resources/views/meta.html.twig';

        if (!file_exists($path) || !is_readable($path)) {
            $output->writeln("<error>file not exist or not readable</error>");
            return false;
        }

        $script = '<link rel="stylesheet" href="//cdn.callbackhunter.com/widget/tracker.css"><script type="text/javascript" src="//cdn.callbackhunter.com/widget/tracker.js" charset="UTF-8"></script><script type="text/javascript">var hunter_code="b7b64a99bb524292c5408181e66a274b";</script>';
        $script .= '<div style="z-index: 9999; font-weight: bold; position: fixed; right: 0px; top: 51px; border-radius: 2px; padding: 10px 15px; background: none repeat scroll 0px 0px rgba(240, 220, 0, 0.8); font-size: 14px; box-shadow: -1px 1px 1px rgba(0, 0, 0, 0.2);"><a target="_blank" href="/demo/index.html">Демо он-лайн бронирования <i class="fa fa-external-link"></i></a></div>';

        $content = file_get_contents($path);
        $content = str_replace('</body>', $script . '</body>', $content);
        file_put_contents($path, $content);

        $output->writeln('Complete. Scripts inserted.');
    }
}