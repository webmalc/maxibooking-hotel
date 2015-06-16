<?php

namespace MBH\Bundle\HotelBundle\EventListener;


use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use MBH\Bundle\HotelBundle\Document\Hotel;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class HotelSubscriber
 * @package MBH\Bundle\HotelBundle\EventListener
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class HotelSubscriber implements EventSubscriber
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            /*'postUpdate',
            'postPersist',
            'postLoad'*/
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof Hotel)
            return;

        $this->synchronizeUploadLogo($document);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof Hotel)
            return;

        $this->synchronizeUploadLogo($document);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if (!$document instanceof Hotel)
            return;

        $this->synchronizeUploadLogo($document);
    }

    /**
     * @return string
     */
    private function getLogoUploadPath()
    {
        /** @var \AppKernel $kernel */
        $kernel = $this->container->get('kernel');
        return realpath($kernel->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'upload');
    }

    private function synchronizeUploadLogo(Hotel $document)
    {
        $logoUploadPath = $this->getLogoUploadPath();

        $fileName = $document->getId();
        $documentFullPath = $logoUploadPath.DIRECTORY_SEPARATOR.$fileName;//.'.'.$logo->getClientOriginalExtension();
        $hasExistUploadedFile = is_file($documentFullPath);

        if ($logo = $document->getLogo()) {
            if(!$hasExistUploadedFile or $documentFullPath != realpath($logo->getPath())) {
                $logo->move($logoUploadPath, $fileName);
            }
        } elseif($hasExistUploadedFile) {
            $logo = new UploadedFile($logoUploadPath.DIRECTORY_SEPARATOR.$fileName, $fileName);
            $document->setLogo($logo);
        }
    }
}