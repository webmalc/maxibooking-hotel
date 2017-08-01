<?php


namespace MBH\Bundle\BaseBundle\DataCollector;


use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use MongoDBODMProxies\__CG__\MBH\Bundle\PackageBundle\Document\OrderDocument;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class InstanceInfoCollector extends DataCollector
{
    /** @var KernelInterface */
    private $kernel;
    /** @var  ContainerInterface */
    private $container;

    private $client;

    public function __construct(KernelInterface $kernel)
    {

        $this->kernel = $kernel;
        $this->container = $this->kernel->getContainer();
        $this->client = $kernel->getClient();

    }


    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'user' => $this->client,
            'dirs' => $this->getInstanceDirs()
        ];
    }

    public function getUser()
    {
        return $this->client;
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
            'vichUpload:destination' => $this->getVichUploadFolder('upload_destination'),
            'vichUpload:uri_prefix' => $this->getVichUploadFolder('uri_prefix'),
            'liip:cache:path' => $this->getLiipCachePath(),
            'orderDocument:path' => realpath((new OrderDocument())->getUploadRootDir($this->client))
        ];

        return $dirs;
    }

    private function getLiipCachePath(string $key = null): string
    {
        $result = 'Не получилось забрать из сервиса настройки';
        return $result;
    }

    private function getVichUploadFolder(string $key): string
    {
        return $this->container->getParameter('vich_uploader.mappings')['upload_image'][$key];
    }

    public function getName()
    {
        return 'client.instance.informer';
    }

}