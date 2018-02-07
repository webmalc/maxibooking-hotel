<?php


namespace MBH\Bundle\BaseBundle\Service;


use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
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

    /** @var FilterManager */
    private $filterManager;

    /**
     * ProtectedImageViewer constructor.
     * @param DownloadHandler $vichDownloadHandler
     * @param UploaderHelper $uploadHelper
     * @param DataManager $liipDataManager
     * @param FilterManager $filterManager
     */
    public function __construct(
        DownloadHandler $vichDownloadHandler,
        UploaderHelper $uploadHelper,
        DataManager $liipDataManager,
        FilterManager $filterManager
    ) {
        $this->vichDownloadHandler = $vichDownloadHandler;
        $this->uploadHelper = $uploadHelper;
        $this->liipDataManager = $liipDataManager;
        $this->filterManager = $filterManager;
    }

    public function streamOutputFile(ProtectedFile $protectedFile): Response
    {
        $file = $this->getBinaryFile($protectedFile);

        return $this->generateRsponse($file);
    }

    public function steamOutputFileWithFilter(ProtectedFile $protectedFile, string $filter = 'scaler')
    {
        $file = $this->getBinaryFile($protectedFile);
        $file = $this->filterManager->applyFilter($file, $filter);

        return $this->generateRsponse($file);
    }

    public function downloadProtectedFile(ProtectedFile $protectedFile): StreamedResponse
    {
        return $this->vichDownloadHandler->downloadObject($protectedFile, 'imageFile');
    }

    private function getBinaryFile(ProtectedFile $protectedFile): ?BinaryInterface
    {
        $path = $this->uploadHelper->asset($protectedFile, 'imageFile');
        $file = $this->liipDataManager->find('protected_scaler', $path);

        return $file;
    }

    private function generateRsponse(BinaryInterface $file): Response
    {
        $headers['Content-Type'] = $file->getMimeType();
        $content = $file->getContent();

        return new Response($content, 200, $headers);
    }

    public function downloadPublicImage(Image $image): StreamedResponse
    {
        return $this->vichDownloadHandler->downloadObject($image, 'imageFile');
    }


}