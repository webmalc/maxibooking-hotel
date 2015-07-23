<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class NoticeStayPlaceXlsGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class NoticeStayPlaceXlsGenerator implements ContainerAwareInterface, DocumentResponseGeneratorInterface
{
    const DEFAULT_LETTER_RANGE = 3;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \Liuggio\ExcelBundle\Factory
     */
    private $phpExcel;

    /**
     * @var \PHPExcel
     */
    private $phpExcelObject;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->phpExcel = $this->container->get('phpexcel');
        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($this->getXlsPath());
    }

    private function getXlsPath()
    {
        return realpath(__DIR__ . '/../../Resources/data/Uvedomlenie_o_pribytii_inostrannogo_grazhdanina_v_mesto_prebyvanija.xls');
    }


    public function generateResponse(array $formData)
    {
        $package = $formData['package'];
        $tourist = $formData['tourist'];
        if (!$package instanceof Package || !$tourist instanceof Tourist) {
            throw new \LogicException();
        }

        $documentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $hotel = $package->getRoomType()->getHotel();

        $this->write($tourist->getLastName(), 'W13');
        $this->write(mb_substr($tourist->getFirstName() . ' ' . $tourist->getPatronymic(), 0, 65), 'W15');
        //$this->write('', 'W15');

        if ($tourist->getCitizenship()) {
            $this->write($tourist->getCitizenship()->getName(), 'AA18');
        }
        if ($birthday = $tourist->getBirthday()) {
            $this->write($birthday->format('d'), 'AE21');
            $this->write($birthday->format('m'), 'AU21');
            $this->write($birthday->format('Y'), 'BG21');
        }
        $this->phpExcelObject->getActiveSheet()->setCellValue('CY21', 'X');

        if ($birthplace = $tourist->getBirthplace()) {
            if ($birthplace->getCountry() && $birthplace->getMainRegion()) {
                $city = $birthplace->getCountry()->getName() . ' ' . $birthplace->getMainRegion();
                // . ' ' . $birthplace->getCity() . ' ' . $tourist->getBirthplace()->getDistrict();
                $this->write(mb_substr($city, 0, 33), 'AE24');
            }

            $this->write($birthplace->getCity() . ' ' . $birthplace->getSettlement(), 'AE27');
        }

        if ($documentRelation = $tourist->getDocumentRelation()) {
            if ($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $this->write($documentTypes[$documentRelation->getType()], 'BC30');
            }
            $this->write($documentRelation->getSeries(), 'DC30');
            $this->write($documentRelation->getNumber(), 'DW30');

            if ($issued = $documentRelation->getIssued()) {
                $this->write($issued->format('d'), 'AA32');
                $this->write($issued->format('m'), 'AQ32');
                $this->write($issued->format('Y'), 'BC32');
            }

            if ($expiry = $documentRelation->getExpiry()) { //Скрок действия
                $this->write($expiry->format('d'), 'CM32');
                $this->write($expiry->format('m'), 'DC32');
                $this->write($expiry->format('Y'), 'DO32');
            }
        }

        //$this->phpExcelObject->getActiveSheet()->setCellValue('W37', 'X');
        //$this->write($tourist->getVisa()->getSeries(), 'DC37');
        //$this->write($tourist->getVisa()->getNumber(), 'DW37');

        $purposeList = [
            'study' => 'CA43',
            'tourism' => 'AY43',
            'work' => 'CM43',
            //'residence' => 'CY43',
            'other' => 'EQ43',
        ];
        if ($package->getPurposeOfArrival() && array_key_exists($package->getPurposeOfArrival(), $purposeList)) {
            $purposeCell = $purposeList[$package->getPurposeOfArrival()];
            $this->phpExcelObject->getActiveSheet()->setCellValue($purposeCell, 'X'); //Цель въезда
        }

        //$this->write('Инженер', 'W45');

        $this->write($tourist->getLastName(), 'W69');
        $this->write(mb_substr($tourist->getFirstName() . ' ' . $tourist->getPatronymic(), 0, 65), 'W71');

        //$this->write('', 'W71');
        $this->write($tourist->getCitizenship(), 'AA74');

        if ($birthday = $tourist->getBirthday()) {
            $this->write($birthday->format('d'), 'AE77');
            $this->write($birthday->format('m'), 'AU77');
            $this->write($birthday->format('Y'), 'BG77');
        }

        $sexList = ['male' => 'DC77', 'female' => 'DW77'];
        if (array_key_exists($tourist->getSex(), $sexList)) {
            $this->phpExcelObject->getActiveSheet()->setCellValue($sexList[$tourist->getSex()], 'X');
        }

        if ($documentRelation = $tourist->getDocumentRelation()) {
            if ($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $this->write($documentTypes[$documentRelation->getType()], 'BC80');
            }
            $this->write($documentRelation->getSeries(), 'DC80');
            $this->write($documentRelation->getNumber(), 'DW80');
        }

        $this->write($hotel->getRegion()->getTitle(), 'AE83');
        if ($hotel->getCity()) {
            //$this->write($hotel->getCity()->getTitle(), 'W86');
            $this->write($hotel->getCity()->getTitle() . ' ' . $hotel->getSettlement(), 'AE88');//Населенный пункт
        }
        $this->write($hotel->getStreet(), 'W91');

        $this->write($hotel->getHouse(), 'S93');
        $this->write($hotel->getCorpus(), 'AQ93');
        //$this->write('', 'BS93'); //строение
        $this->write($hotel->getFlat(), 'CU93');

        $this->write($package->getBegin()->format('d'), 'AQ95');
        $this->write($package->getBegin()->format('m'), 'BG95');
        $this->write($package->getBegin()->format('Y'), 'BS95');


        // two sheet
        $this->phpExcelObject->setActiveSheetIndex(1);

        if ($hotel->getRegion()) {
            $this->write($hotel->getRegion()->getTitle(), 'AE14');
        }
        if ($hotel->getCity()) {
            $this->write($hotel->getCity()->getTitle(), 'W17'); //Район Город или другой
        }
        $this->write($hotel->getSettlement(), 'AE19'); //Населенный пункт
        $this->write($hotel->getStreet(), 'W22');
        $this->write($hotel->getHouse(), 'S24');
        $this->write($hotel->getCorpus(), 'AQ24');
        $this->write('', 'BS24');
        $this->write($hotel->getFlat(), 'CU24');
        //$this->write('94957548864', 'DS24');
        $this->phpExcelObject->getActiveSheet()->setCellValue('EE26', 'X');

        if ($organization = $hotel->getOrganization()) {
            $this->write($organization->getName(), 'AA51');
            $this->write($organization->getInn(), 'S60');
        }

        $this->write($package->getEnd()->format('d'), 'DO69');
        $this->write($package->getEnd()->format('m'), 'EE69');
        $this->write($package->getEnd()->format('Y'), 'EQ69');

        $this->phpExcelObject->setActiveSheetIndex(0);


        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($this->getXlsPath());

        $drawing = new \PHPExcel_Worksheet_Drawing();
        $drawing->setPath(dirname($this->getXlsPath()).'/square.jpg');
        //$drawing->setName('Sample image');
        //$drawing->setDescription('Sample image');
        $drawing->setHeight(28);
        $drawing->setWidth(28);
        $drawing->setResizeProportional(true);
        $drawing->setOffsetX(43.6);
        $drawing->setOffsetY(33);
        $drawing->setWorksheet($this->phpExcelObject->getActiveSheet());

        $response = $this->phpExcel->createStreamedResponse(
            $this->phpExcel->createWriter($this->phpExcelObject, 'Excel2007'));

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Helper::translateToLat($tourist->getFullName()).'.xls'
        );
        $response->headers->set('Content-Type', 'application/ms-excel; charset=utf-8');//text/vnd.ms-excel
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param $word
     * @param string $startCell
     * @param int $range
     * @throws \PHPExcel_Exception
     */
    protected function write($word, $startCell = 'A0', $range = self::DEFAULT_LETTER_RANGE)
    {
        $word = mb_strtoupper($word, 'UTF-8');
        $word = preg_replace('~[^A-ZА-Я 0-9]+~', '', $word);

        list ($rowLetter, $lineNumber) = $this->explodeCell($startCell);

        $activeCell = $startCell;
        $activeRowLetter = $rowLetter;
        foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $letter) {
            $letter = trim($letter);
            $this->phpExcelObject->getActiveSheet()->setCellValue($activeCell, $letter);

            $rowLetterIndex = \PHPExcel_Cell::columnIndexFromString($activeRowLetter);
            $rowLetterIndex += $range;
            $activeRowLetter = \PHPExcel_Cell::stringFromColumnIndex($rowLetterIndex);
            $activeCell = $activeRowLetter . $lineNumber;
        }
    }

    /**
     * Get separate Letter and number of cell
     * @param $cell
     * @return array [letter, number]
     */
    protected function explodeCell($cell)
    {
        preg_match('~([A-Z]+)(\d+)~', $cell, $result);
        if (count($result) !== 3) {
            throw new \InvalidArgumentException("Passed Xls cell is not valid");
        }
        array_shift($result); //shift first element
        return $result;
    }
}