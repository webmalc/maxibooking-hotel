<?php


namespace MBH\Bundle\BaseBundle\DataCollector;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class InstanceInfoCollector extends DataCollector
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'user' => $request->server->get('MB_CLIENT'),
            'dirs' => $this->getInstanceDirs()
        ];
    }

    public function getUser()
    {
        return $this->data['user'];
    }

    public function getDirs()
    {
        return $this->data['dirs'];
    }

    public function getInstanceDirs(): array
    {
        /** @var Kernel $kernel */
        $dirs = [
            'cacheDir' => $this->kernel->getCacheDir(),
            'logDir' => $this->kernel->getLogDir(),
            'vichUpload' => $this->getVichUploadFolder()
        ];

        return $dirs;
    }

    private function getVichUploadFolder()
    {

        $container = $this->kernel->getContainer();
        $dir = $container->getParameter('vich_uploader.mappings')['upload_image']['upload_destination'];

        return $dir;
    }

    public function getName()
    {
        return 'client.instance.informer';
    }

}