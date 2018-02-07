<?php


namespace MBH\Bundle\BaseBundle\Service;


use Liip\ImagineBundle\Imagine\Data\DataManager;
use MBH\Bundle\BaseBundle\Document\Image;
use MBH\Bundle\BaseBundle\Document\ProtectedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vich\UploaderBundle\Handler\DownloadHandler;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ProtectedFileViewer
{
    /** @var DownloadHandler */
    private $vichDownloadHandler;

    /** @var UploaderHelper */
    private $uploadHelper;

    /** @var  DataManager */
    private $liipDataManager;

    /**
     * ProtectedImageViewer constructor.
     * @param DownloadHandler $vichDownloadHandler
     * @param UploaderHelper $uploadHelper
     * @param DataManager $liipDataManager
     */
    public function __construct(
        DownloadHandler $vichDownloadHandler,
        UploaderHelper $uploadHelper,
        DataManager $liipDataManager
    ) {
        $this->vichDownloadHandler = $vichDownloadHandler;
        $this->uploadHelper = $uploadHelper;
        $this->liipDataManager = $liipDataManager;
    }

    public function streamOutputFile(ProtectedFile $protectedFile): Response
    {
        $path = $this->uploadHelper->asset($protectedFile, 'imageFile');
        $file = $this->liipDataManager->find('protected_scaler', $path);
        $headers['Content-Type'] = $file->getMimeType();
        $content = $file->getContent();

        return new Response($content, 200, $headers);
    }

    public function downloadProtectedFile(ProtectedFile $protectedFile): StreamedResponse
    {
        return $this->vichDownloadHandler->downloadObject($protectedFile, 'imageFile');
    }

    public function downloadPublicImage(Image $image): StreamedResponse
    {
        return $this->vichDownloadHandler->downloadObject($image, 'imageFile');
    }

    public function steamOutputFileWithFilter(ProtectedFile $protectedFile, string $filter)
    {
    }


}