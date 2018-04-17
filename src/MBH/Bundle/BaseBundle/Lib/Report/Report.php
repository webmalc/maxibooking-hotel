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
    const VERTICAL_SCROLLABLE_CLASS = 'vertical-scrollable';
    const HORIZONTAL_SCROLLABLE_CLASS = 'horizontal-scrollable';

    protected $isSuccess = true;
    protected $errors;
    protected $tables = [];
    /** @var TwigEngine */
    private $twigEngine;
    protected $title;
    protected $rowTitles;
    protected $commonRowTitles = [];

    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }

    /**
     * @return mixed
     */
    public function getCommonRowTitlesAsJson()
    {
        return addslashes(json_encode($this->commonRowTitles));
    }

    /**
     * @param array $commonRowTitles
     * @return Report
     */
    public function setCommonRowTitles(array $commonRowTitles)
    {
        $this->commonRowTitles = $commonRowTitles;

        return $this;
    }

    /**
     * @param array $rowTitles
     * @return Report
     */
    public function setRowTitles(array $rowTitles)
    {
        $this->rowTitles = $rowTitles;

        return $this;
    }

    /**
     * @return string
     */
    public function getRowTitlesAsJson()
    {
        return addslashes(json_encode($this->rowTitles));
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Report
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param $error
     * @param bool $isReportSuccess
     * @return Report
     */
    public function addError($error, $isReportSuccess = false)
    {
        $this->errors[] = $error;
        if (!$isReportSuccess) {
            $this->isSuccess = false;
        }

        return $this;
    }

    /**
     * @param bool $forMail
     * @return ReportTable
     */
    public function addReportTable($forMail = false)
    {
        $reportTable = (new ReportTable())->setIsForMail($forMail);
        $this->tables[] = $reportTable;

        return $reportTable;
    }

    public function generate($withJson)
    {
        return $this->twigEngine->render('@MBHBase/Report/report_table.html.twig', ['report' => $this, 'withJson' => $withJson]);
    }

    /**
     * @param bool $withJson
     * @return Response
     */
    public function generateReportTableResponse($withJson = true)
    {
        return (new Response())->setContent($this->generate($withJson));
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