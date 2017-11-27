<?php

namespace MBH\Bundle\BaseBundle\Service\Export;

use Symfony\Component\HttpFoundation\Response;

class ZipManager
{
    public function getAttachedZipResponse($zipFileName, $attachedFileName)
    {
        $response = new Response(file_get_contents($zipFileName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachedFileName . '"');
        $response->headers->set('Content-length', filesize($zipFileName));

        return $response;
    }

    public function writeToZip($stringsToWriteByNames, $zipName)
    {
        $zip = new \ZipArchive();

        $zip->open($zipName, \ZipArchive::CREATE);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $zip->deleteIndex($i);
        }

        foreach ($stringsToWriteByNames as $fileName => $stringToWrite) {
            $zip->addFromString($fileName, $stringToWrite);
        }

        $zip->close();

        return $zip;
    }
}