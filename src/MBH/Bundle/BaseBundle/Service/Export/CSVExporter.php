<?php
/**
 * Created by PhpStorm.
 * User: danya
 * Date: 28.07.17
 * Time: 11:23
 */

namespace MBH\Bundle\BaseBundle\Service\Export;

use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class CSVExporter
{
    /** @var  ExportDataHandler */
    private $dataHandler;
    /** @var  TranslatorInterface */
    private $translator;

    public function __construct(ExportDataHandler $dataHandler, TranslatorInterface $translator) {
        $this->dataHandler = $dataHandler;
        $this->translator = $translator;
    }

    public function exportToCSV(Builder $qb, $entityName, $exportedFields = null): Response
    {
        $rawMongoData = $qb
            ->limit(0)
            ->skip(0)
            ->hydrate(false)
            ->getQuery()
            ->execute()
            ->toArray();

        $handledData = $this->dataHandler->handleRawMongoData($rawMongoData, $exportedFields, $entityName);

        $exportedFieldsNames = [];
        foreach ($exportedFields as $exportedField) {
            $exportedFieldsNames[] = $this->translator->trans($exportedField);
        }

        $rows = [join(";", $exportedFieldsNames)];
        foreach ($handledData as $handledEntityData) {
            $rows[] = join(';', $handledEntityData);
        }
        $content = join("\n", $rows);
        $content = iconv('UTF-8', 'windows-1251//IGNORE', $content);
        $response = new Response($content);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }
}