<?php

namespace MBH\Bundle\BaseBundle\Lib\Report;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Report
 * @package MBH\Bundle\BaseBundle\Lib\Report
 */
class Report
{
    protected $isSuccess = true;
    protected $errors;
    protected $tables = [];
    /** @var TwigEngine */
    private $twigEngine;

    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }

    public static function createReportTable()
    {
        return new ReportTable();
    }

    public function addError($error, $isReportSuccess = false)
    {
        $this->errors[] = $error;
        if (!$isReportSuccess) {
            $this->isSuccess = false;
        }

        return $this;
    }

    public function addReportTable()
    {
        $reportTable = new ReportTable();
        $this->tables[] = $reportTable;

        return $reportTable;
    }

    public function generate()
    {
        return $this->twigEngine->render('@MBHBase/Report/report_table.html.twig', ['report' => $this]);
    }

    public function generateReportTableResponse()
    {
        return (new Response())->setContent($this->generate());
    }

    /**
     * @return bool
     */
    public function isSuccess(): ?bool
    {
        return $this->isSuccess;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return ReportTable[]
     */
    public function getTables(): ?array
    {
        return $this->tables;
    }

}