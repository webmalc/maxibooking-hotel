<?php

namespace MBH\Bundle\BaseBundle\Service\Export;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class ZipManager
{
    /**
     * @param $zipFileName
     * @param $attachedFileName
     * @return Response
     */
    public function getAttachedZipResponse($zipFileName, $attachedFileName)
    {
        $response = new Response(file_get_contents($zipFileName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachedFileName . '"');
        $response->headers->set('Content-length', filesize($zipFileName));

        return $response;
    }

    /**
     * @param $stringsToWriteByNames
     * @param $zipName
     * @return StreamedResponse
     */
    public function writeToStreamedResponse($stringsToWriteByNames, $zipName)
    {
        $response = new StreamedResponse(function () use ($stringsToWriteByNames, $zipName) {
            $zipStream = new ZipStream($zipName, [
                'content_type' => 'application/octet-stream'
            ]);

            foreach ($stringsToWriteByNames as $fileName => $stringToWrite) {
                $zipStream->addFile($fileName, $stringToWrite);
            }

            $zipStream->finish();
        });

        return $response;
    }
}