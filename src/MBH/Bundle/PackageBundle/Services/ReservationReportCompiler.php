<?php

namespace MBH\Bundle\PackageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\BaseBundle\Lib\Report\Report;
use Symfony\Component\Translation\TranslatorInterface;

class ReservationReportCompiler
{
    private $dm;
    private $report;
    private $translator;

    public function __construct(DocumentManager $dm, Report $report, TranslatorInterface $translator) {
        $this->dm = $dm;
        $this->report = $report;
        $this->translator = $translator;
    }

    public function generate(\DateTime $periodBegin, \DateTime $periodEnd, \DateTime $date)
    {
        return $this->report;
    }
}