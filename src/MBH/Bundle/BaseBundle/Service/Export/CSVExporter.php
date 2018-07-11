<?php

namespace MBH\Bundle\BaseBundle\Service\Export;

use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class CSVExporter
{
    const DEFAULT_DELIMITER = ';';

    /** @var  RawMongoDataHandler */
    private $dataHandler;
    /** @var  TranslatorInterface */
    private $translator;

    public function __construct(RawMongoDataHandler $dataHandler, TranslatorInterface $translator) {
        $this->dataHandler = $dataHandler;
        $this->translator = $translator;
    }

    /**
     * @param Builder $qb
     * @param $entityName
     * @param null $exportedFields
     * @return Response
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function exportToCSVResponse(Builder $qb, $entityName, $exportedFields = null): Response
    {
        $rawMongoData = $qb
            ->limit(0)
            ->skip(0)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $handledData = $this->dataHandler->handleRawMongoData($rawMongoData, $entityName, $exportedFields);

        $exportedFieldsNames = [];
        foreach ($exportedFields as $exportedField) {
            $exportedFieldsNames[] = $this->translator->trans($exportedField);
        }

        $rows = [join(";", $exportedFieldsNames)];
        foreach ($handledData as $handledEntityData) {
            $rows[] = join(';', $handledEntityData);
        }

        $content = join("\n", $rows);
        $content =  mb_convert_encoding($content,'windows-1251//TRANSLIT', 'UTF-8');

        return $this->getCsvAttachmentResponse($content);
    }

    /**
     * @param $content
     * @param string $fileName
     * @return Response
     */
    public function getCsvAttachmentResponse($content, $fileName = 'export')
    {
        $response = new Response($content);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $fileName . '.csv"');

        return $response;
    }

    /**
     * @param array $dataArray
     * @param string $filePath
     * @param string $delimiter
     */
    public function writeToCsv(array $dataArray, $filePath, $delimiter = self::DEFAULT_DELIMITER)
    {
        $fp = fopen($filePath, 'w');

        foreach ($dataArray as $rowsData) {
            fputcsv($fp, $rowsData, $delimiter);
        }

        fclose($fp);
    }
}