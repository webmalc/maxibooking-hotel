<?php

namespace MBH\Bundle\PackageBundle\Component;


use MBH\Bundle\BaseBundle\Lib\Exception;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NoticeStayPlaceXlsGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class NoticeStayPlaceXlsGenerator implements ContainerAwareInterface
{
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

    const DEFAULT_LETTER_RANGE = 3;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->phpExcel = $this->container->get('phpexcel');

        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($this->getXlsPath());
    }

    private function getXlsPath()
    {
        return realpath(__DIR__.'/../Resources/data/Uvedomlenie_o_pribytii_inostrannogo_grazhdanina_v_mesto_prebyvanija.xls');
    }

    public function generateResponse(Package $package, Tourist $tourist)
    {
        $documentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $hotel = $package->getRoomType()->getHotel();

        if($fullName = $tourist->getFullName()) {
            $fullName = mb_substr($fullName, 0, 65);
            if(mb_strlen($fullName) > 35) {
                $fullName = explode(' ', $fullName);
                $lastName = array_pop($fullName);
                $this->write(implode(' ', $fullName), 'W13');
                $this->write($lastName, 'W15');
            }else {
                $this->write($fullName, 'W13');
            }
        }
        //$this->write('', 'W15');

        if($tourist->getCitizenship()) {
            $this->write($tourist->getCitizenship()->getName(), 'AA18');
        }
        if($tourist->getBirthday()) {
            $this->write($tourist->getBirthday()->format('d'), 'AE21');
            $this->write($tourist->getBirthday()->format('m'), 'AU21');
            $this->write($tourist->getBirthday()->format('Y'), 'BG21');
        }
        $this->phpExcelObject->getActiveSheet()->setCellValue('CY21', 'X');

        if($birthplace = $tourist->getBirthplace()) {
            if($birthplace->getCountry() && $birthplace->getMainRegion()) {
                $city = $birthplace->getCountry()->getName() . ' ' . $birthplace->getMainRegion();
                // . ' ' . $birthplace->getCity() . ' ' . $tourist->getBirthplace()->getDistrict();
                $this->write(mb_substr($city, 0, 33), 'AE24');
            }

            $this->write($birthplace->getCity() . ' ' . $birthplace->getSettlement(), 'AE27');
        }

        if($documentRelation = $tourist->getDocumentRelation()) {
            if($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $this->write($documentTypes[$documentRelation->getType()], 'BC30');
            }
            $this->write($documentRelation->getSeries(), 'DC30');
            $this->write($documentRelation->getNumber(), 'DW30');

            if($issued = $documentRelation->getIssued()) {
                $this->write($issued->format('d'), 'AA32');
                $this->write($issued->format('m'), 'AQ32');
                $this->write($issued->format('Y'), 'BC32');
            }

            if($expiry = $documentRelation->getExpiry()) { //Скрок действия
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
            'tourism'=> 'AY43',
            'work' => 'CM43',
            //'residence' => 'CY43',
            'other' => 'EQ43',
        ];
        if($package->getPurposeOfArrival() && array_key_exists($package->getPurposeOfArrival(), $purposeList)) {
            $this->phpExcelObject->getActiveSheet()->setCellValue($purposeList[$package->getPurposeOfArrival()], 'X'); //Цель въезда
        }

        //$this->write('Инженер', 'W45');

        /*
        $this->write('02'), 'AI47');
        $this->write('02'), 'AY47');
        $this->write('02'), 'BK47');

        $this->write('02', 'DO47');
        $this->write('02', 'EE47');
        $this->write('02', 'EQ47');

        //$this->write('5653', 'AQ49'); //Миграционная карта
        //$this->write('2698432', 'BK49');
        //$this->write('Сведения о законных', 'AA51');
        //$this->write($hotel->getCity() + ' ' +$hotel->getSettlement() + ' ' + $hotel->getStreet() + ' ' + $hotel->getHouse(), 'AA57');
        */

        if($fullName = $tourist->getFullName()) {
            $fullName = mb_substr($fullName, 0, 65);
            if(mb_strlen($fullName) > 35) {
                $fullName = explode(' ', $fullName);
                $lastName = array_pop($fullName);
                $this->write(implode(' ', $fullName), 'W69');
                $this->write($lastName, 'W71');
            }else {
                $this->write($fullName, 'W69');
            }
        }

        //$this->write('', 'W71');
        $this->write($tourist->getCitizenship(), 'AA74');

        if($tourist->getBirthday()) {
            $this->write($tourist->getBirthday()->format('d'), 'AE77');
            $this->write($tourist->getBirthday()->format('m'), 'AU77');
            $this->write($tourist->getBirthday()->format('Y'), 'BG77');
        }

        $sexList = ['male' => 'DC77', 'female' => 'DW77'];
        if(array_key_exists($tourist->getSex(), $sexList)) {
            $this->phpExcelObject->getActiveSheet()->setCellValue($sexList[$tourist->getSex()], 'X');
        }

        if($documentRelation = $tourist->getDocumentRelation()) {
            if($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $this->write($documentTypes[$documentRelation->getType()], 'BC80');
            }
            $this->write($documentRelation->getSeries(), 'DC80');
            $this->write($documentRelation->getNumber(), 'DW80');
        }

        $this->write($hotel->getRegion()->getTitle(), 'AE83');
        if($hotel->getCity()) {
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

        if($hotel->getRegion()) {
            $this->write($hotel->getRegion()->getTitle(), 'AE14');
        }
        if($hotel->getCity()) {
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

        /*$this->write($organization->getDirectorFio(), 'W28');
        $this->write('Петрович', 'W31');
        $this->write('24', 'DO28');
        $this->write('02', 'EE28');
        $this->write('1979', 'EQ28');

        $this->write('Паспорт', 'BC34');
        $this->write('3587', 'DC37');
        $this->write('78574324', 'DW37');

        $this->write('11', 'AA36');
        $this->write('02', 'AQ36');
        $this->write('2000', 'BC36');

        $this->write('11', 'CM36');
        $this->write('02', 'DC36');
        $this->write('2025', 'DO36');

        $this->write('Республика Татарстан', 'AE39');
        $this->write('Суздолькая', 'W47');
        $this->write('9', 'S49');
        $this->write('', 'AQ49');
        $this->write('', 'BS49');
        $this->write('45', 'CU49');
        $this->write('85967748555', 'DS49');
        */

        if($organization = $hotel->getOrganization()) {
            $this->write($organization->getName(), 'AA51');
            $this->write($organization->getInn(), 'S60');

            //$this->write($organization->getDirectorFio(), 'W71');
            //$this->write('Петрович', 'W73');
        }

        $this->write($package->getEnd()->format('d'), 'DO69');
        $this->write($package->getEnd()->format('m'), 'EE69');
        $this->write($package->getEnd()->format('Y'), 'EQ69');

        $this->phpExcelObject->setActiveSheetIndex(0);
        return $this->phpExcel->createStreamedResponse($this->phpExcel->createWriter($this->phpExcelObject));
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
        foreach(preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $letter) {
            $letter = trim($letter);
            $this->phpExcelObject->getActiveSheet()->setCellValue($activeCell, $letter);

            $rowLetterIndex = \PHPExcel_Cell::columnIndexFromString($activeRowLetter);
            $rowLetterIndex += $range;
            $activeRowLetter = \PHPExcel_Cell::stringFromColumnIndex($rowLetterIndex);
            $activeCell = $activeRowLetter.$lineNumber;
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
        if(count($result) !== 3) {
            throw new \InvalidArgumentException("Passed Xls cell is not valid");//Exception
        }
        array_shift($result); //shift first element
        return $result;
    }
}