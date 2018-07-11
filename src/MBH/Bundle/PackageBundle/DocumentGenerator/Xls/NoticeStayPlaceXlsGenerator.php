<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls;


use MBH\Bundle\BaseBundle\Service\Helper;
use MBH\Bundle\BillingBundle\Service\BillingApi;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\DocumentGenerator\DocumentResponseGeneratorInterface;
use MBH\Bundle\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class NoticeStayPlaceXlsGenerator

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
    /** @var  BillingApi */
    private $billing;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->billing = $container->get('mbh.billing.api');
        $this->phpExcel = $this->container->get('phpexcel');
        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($this->getXlsPath());
    }

    /**
     * @return array|string
     */
    private function getXlsPath()
    {
        return $this->container->get('file_locator')->locate('@MBHPackageBundle/Resources/data/Uvedomlenie_o_pribytii_inostrannogo_grazhdanina_v_mesto_prebyvanija2.XLS');
    }


    public function generateResponse(array $formData)
    {
        $package = $formData['package'];
        $tourist = $formData['tourist'];
        $user = $formData['user'];
        if (!$package instanceof Package || !$tourist instanceof Tourist || !$user instanceof User) {
            throw new \LogicException();
        }

        $documentTypes = $this->container->get('mbh.fms_dictionaries')->getDocumentTypes();
        $hotel = $package->getRoomType()->getHotel();

        $this->write($tourist->getLastName(), 'W13');
        $this->write(mb_substr($tourist->getFirstName() . ' ' . $tourist->getPatronymic(), 0, 65), 'W15');
        //$this->write('', 'W15');

        if ($tourist->getCitizenshipTld()) {
            $this->write($this->billing->getCountryByTld($tourist->getCitizenshipTld())->getName(), 'AA18');
        }
        if ($birthday = $tourist->getBirthday()) {
            $this->write($birthday->format('d'), 'AE21');
            $this->write($birthday->format('m'), 'AU21');
            $this->write($birthday->format('Y'), 'BG21');
        }
        $sexList = ['male' => 'CY21', 'female' => 'DS21'];
        if (array_key_exists($tourist->getSex(), $sexList)) {
            $this->phpExcelObject->getActiveSheet()->setCellValue($sexList[$tourist->getSex()], 'X');
        }

        if ($birthplace = $tourist->getBirthplace()) {
            if ($birthplace->getCountryTld() && $birthplace->getMainRegion()) {
                $city = $this->billing->getCountryByTld($birthplace->getCountryTld())->getName() . ' ' . $birthplace->getMainRegion();
                // . ' ' . $birthplace->getCity() . ' ' . $tourist->getBirthplace()->getDistrict();
                $this->write(mb_substr($city, 0, 33), 'AE24');
            }

            $this->write($birthplace->getCity() . ' ' . $birthplace->getSettlement(), 'AE27');
        }

        if ($documentRelation = $tourist->getDocumentRelation()) {
            if ($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $documentTypeString = $documentRelation->getType() === 103012 ? 'Паспорт ИГ' : $documentTypes[$documentRelation->getType()];
                $this->write($documentTypeString, 'BC30');
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
            'service' => 'AM43',//'Служебная',
            'tourism' => 'AY43',//'Туризм',
            'business' => 'BO43',//'Деловая',
            'study' => 'CA43',//'Учеба',
            'work' => 'CM43', //'Работа',
            'private' => 'CY43',//'Частная',
            'residence' => 'DK43',//'Транзит',
            'humanitarian' => 'EE43', //'Гуманитарная',
            'other' => 'EQ43' //'Другая'
        ];
        if ($package->getPurposeOfArrival() && array_key_exists($package->getPurposeOfArrival(), $purposeList)) {
            $purposeCell = $purposeList[$package->getPurposeOfArrival()];
            $this->phpExcelObject->getActiveSheet()->setCellValue($purposeCell, 'X'); //Цель въезда
        }

        if($visa = $tourist->getVisa()) {
            $types = [
                'visa' => 'W37',
                'residence' => 'AY37',
                'temporary_residence_permit' => 'CM37',
            ];
            if(array_key_exists($visa->getType(), $types)) {
                $this->write('X', $types[$visa->getType()]);
            }

            $this->write($visa->getProfession(), 'W45');
            $this->write($visa->getSeries(), 'DC37');
            $this->write($visa->getNumber(), 'DW37');

            if($issued = $visa->getIssued()) {
                $this->write($issued->format('d'), 'AA40');
                $this->write($issued->format('m'), 'AQ40');
                $this->write($issued->format('Y'), 'BC40');
            }

            if($expiry = $visa->getExpiry()) {
                $this->write($expiry->format('d'), 'CM40');
                $this->write($expiry->format('m'), 'DC40');
                $this->write($expiry->format('Y'), 'DO40');
            }

            if ($visa->getArrivalTime()) {
                $this->write($visa->getArrivalTime()->format('d'), 'AI47');
                $this->write($visa->getArrivalTime()->format('m'), 'AY47');
                $this->write($visa->getArrivalTime()->format('Y'), 'BK47');
            }

            if ($visa->getDepartureTime()) {
                $this->write($visa->getDepartureTime()->format('d'), 'DO47');
                $this->write($visa->getDepartureTime()->format('m'), 'EE47');
                $this->write($visa->getDepartureTime()->format('Y'), 'EQ47');
            }
        }

        if($migration = $tourist->getMigration()) {
            $this->write($migration->getSeries(), 'AQ49');
            $this->write($migration->getNumber(), 'BK49');

            $this->write($migration->getRepresentative(), 'AA51', 19);
            $this->write($migration->getAddress(), 'AA57', 19);
        }

        $this->write($tourist->getLastName(), 'W69');
        $this->write(mb_substr($tourist->getFirstName() . ' ' . $tourist->getPatronymic(), 0, 65), 'W71');
        //$this->write($tourist->getPatronymic(), 'W73');

        //$this->write('', 'W71');
        $this->write($this->billing->getCountryByTld($tourist->getCitizenshipTld())->getName(), 'AA74');

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
                $documentTypeString = $documentRelation->getType() === 103012 ? 'Паспорт ИГ' : $documentTypes[$documentRelation->getType()];
                $this->write($documentTypeString, 'BC80');
            }
            $this->write($documentRelation->getSeries(), 'DC80');
            $this->write($documentRelation->getNumber(), 'DW80');
        }

        if($hotel->getRegionId()) {
            $this->write($this->billing->getRegionById($hotel->getRegionId())->getName(), 'AE83');
        }
        if ($hotel->getCityId()) {
            //$this->write($hotel->getCity()->getTitle(), 'W86');
            $this->write($this->billing->getCityById($hotel->getCityId())->getName() . ' ' . $hotel->getSettlement(), 'AE88');//Населенный пункт
        }
        $this->write($hotel->getStreet(), 'W91');

        $this->write($hotel->getHouse(), 'S93');
        $this->write($hotel->getCorpus(), 'AQ93');
        //$this->write('', 'BS93'); //строение
        $this->write($hotel->getFlat(), 'CU93');

        $this->write($package->getEnd()->format('d'), 'AQ95');
        $this->write($package->getEnd()->format('m'), 'BG95');
        $this->write($package->getEnd()->format('Y'), 'BS95');


        // two sheet
        $this->phpExcelObject->setActiveSheetIndex(1);

        if ($hotel->getRegionId()) {
            $this->write($this->billing->getRegionById($hotel->getRegionId())->getName(), 'AE14');
        }

        //Район
        //$this->write($hotel->getSettlement(), 'W17');

        if ($hotel->getCityId()) {
            $this->write($this->billing->getCityById($hotel->getCityId())->getName(), 'AE19');  // Город или другой населенный пункт
        }
        $this->write($hotel->getStreet(), 'W22');
        $this->write($hotel->getHouse(), 'S24');
        $this->write($hotel->getCorpus(), 'AQ24');
        $this->write('', 'BS24');
        $this->write($hotel->getFlat(), 'CU24');
        //$this->write('94957548864', 'DS24');
        $this->phpExcelObject->getActiveSheet()->setCellValue('EE26', 'X');

        $this->write($user->getLastName(), 'W28');
        $this->write($user->getFirstName().' '.$user->getPatronymic(), 'W31');
        if($birthplace = $user->getBirthday()) {
            $this->write($birthplace->format('d'), 'DO28');
            $this->write($birthplace->format('m'), 'EE28');
            $this->write($birthplace->format('Y'), 'EQ28');
        }

        if($documentRelation = $user->getDocumentRelation()) {
            if ($documentRelation->getType() && array_key_exists($documentRelation->getType(), $documentTypes)) {
                $this->write($documentTypes[$documentRelation->getType()], 'BC34');
            }

            $this->write($documentRelation->getSeries(), 'DC34');
            $this->write($documentRelation->getNumber(), 'DW34');
            if($issued = $documentRelation->getIssued()) {
                $this->write($issued->format('d'), 'AA36');
                $this->write($issued->format('m'), 'AQ36');
                $this->write($issued->format('Y'), 'BC36');
            }
            if($expiry = $documentRelation->getExpiry()) {
                $this->write($expiry->format('d'), 'CM36');
                $this->write($expiry->format('m'), 'DC36');
                $this->write($expiry->format('Y'), 'DO36');
            }
        }

        $addressObjectDecomposed = $user->getAddressObjectDecomposed();
        if($addressObjectDecomposed && $addressObjectDecomposed->getRegionId()) {
            if($region = $this->billing->getRegionById($addressObjectDecomposed->getRegionId())) {
                $this->write($region->getName(), 'AE39');
            }

            if($district = $addressObjectDecomposed->getDistrict()) { //Район
                $this->write($district, 'W42');
            }
            $city = $addressObjectDecomposed->getSettlement();
            if(!$city) {
                $city = $addressObjectDecomposed->getCity();
            }
            if($city) { //Город или другой населенный пункт
                $this->write($city, 'AE44');
            }

            if($street = $addressObjectDecomposed->getStreet()) {
                $this->write($street, 'W47');
            }

            $this->write($addressObjectDecomposed->getHouse(), 'S49');
            $this->write($addressObjectDecomposed->getCorpus(), 'AQ49');
            $this->write($addressObjectDecomposed->getFlat(), 'CU49');
        }

        //$this->write($user->get, 'DS49');phone


        if ($organization = $hotel->getOrganization()) {
            $firstLineLength = 23;
            $secondLineLength = 27;

            $organizationName = strlen($organization->getName()) > ($firstLineLength + $secondLineLength)
                ? $organization->getShortName()
                : $organization->getName();

            $this->write(mb_substr($organizationName, 0, $firstLineLength), 'AA51');
            if (strlen($organizationName) > $firstLineLength) {
                $this->write(substr($organizationName, $firstLineLength), 'K54');
            }
            $this->write($organization->getInn(), 'S60');
        }

        if($hotel) {
            $address = [];
            if($hotel->getCityId()) {
                $city = $this->billing->getCityById($hotel->getCityId());
                $address[] = $city->getName();

                if($hotel->getStreet()) {
                    $address[] = 'ул '. $hotel->getStreet();

                    if($hotel->getHouse()) {
                        $address[] = 'д '. $hotel->getHouse();
                    }
                }
            }

            if($address) {
                /*$this->write(array_shift($address), 'AA56');
                $text = implode(' ', $address);
                $this->write($text, 'K58');*/
                $this->write(implode(' ', $address), 'AA56', 24, ['K58']);
            }
        }

        $this->write($package->getEnd()->format('d'), 'DO69');
        $this->write($package->getEnd()->format('m'), 'EE69');
        $this->write($package->getEnd()->format('Y'), 'EQ69');

        $this->write($user->getLastName(), 'W71');
        $this->write($user->getFirstName().' '.$user->getPatronymic(), 'W73');

        $this->phpExcelObject->setActiveSheetIndex(0);
        $this->setSquaresOnActiveSheet();
        $this->phpExcelObject->setActiveSheetIndex(1);
        $this->setSquaresOnActiveSheet();

        $this->phpExcelObject->setActiveSheetIndex(0);

        $response = $this->phpExcel->createStreamedResponse(
            $this->phpExcel->createWriter($this->phpExcelObject));//, 'Excel2007'

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Helper::translateToLat($tourist->getFullName()).'.xls'
        );
        $response->headers->set('Content-Type', 'application/ms-excel; charset=utf-8');//text/vnd.ms-excel
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function squareDrawingPrototype()
    {
        $drawingPrototype = new \PHPExcel_Worksheet_Drawing();
        $drawingPrototype->setPath(dirname($this->getXlsPath()).'/square.jpg');
        $drawingPrototype->setResizeProportional(false);
        $drawingPrototype->setHeight(27);
        $drawingPrototype->setWidth(29);
        return $drawingPrototype;
    }

    protected function setSquaresOnActiveSheet()
    {
        $drawingPrototype = $this->squareDrawingPrototype();
        $sheet = $this->phpExcelObject->getActiveSheet();

        $drawing = clone($drawingPrototype);
        //$drawing->setOffsetX(43.6);
        //$drawing->setOffsetY(33);
        $drawing->setCoordinates('L3');
        $drawing->setWorksheet($sheet);

        $drawing = clone($drawingPrototype);
        //$drawing->setOffsetX(645);
        //$drawing->setOffsetY(33);
        $drawing->setCoordinates('EY3');
        $drawing->setWorksheet($sheet);

        $drawing = clone($drawingPrototype);
        //$drawing->setOffsetX(43.6);
        //$drawing->setOffsetY(1080);
        $drawing->setCoordinates($this->phpExcelObject->getActiveSheetIndex() == 0 ? 'L97' : 'L83');
        $drawing->setWorksheet($sheet);

        $drawing = clone($drawingPrototype);
        //$drawing->setOffsetY(1080);
        //$drawing->setOffsetX(525);
        $drawing->setCoordinates($this->phpExcelObject->getActiveSheetIndex() == 0 ? 'DV97' : 'DV83');
        $drawing->setWorksheet($sheet);

        $drawing = clone($drawingPrototype);
        //$drawing->setOffsetY(1080);
        //$drawing->setOffsetX(645);
        $drawing->setCoordinates($this->phpExcelObject->getActiveSheetIndex() == 0 ? 'EZ97' : 'EZ83');
        $drawing->setWorksheet($sheet);
    }

    /**
     * @param $word
     * @param string $startCell
     * @param null|int $breakLineLength
     * @param array $nextCells
     * @param int $range
     * @throws \PHPExcel_Exception
     */
    protected function write($word, $startCell = 'A0', $breakLineLength = null, $nextCells = [], $range = self::DEFAULT_LETTER_RANGE)
    {
        $word = mb_strtoupper($word, 'UTF-8');
        $word = preg_replace('~[^A-ZА-Я 0-9]+~', '', $word);

        list ($rowLetter, $lineNumber) = $this->explodeCell($startCell);

        $activeCell = $startCell;
        $activeRowLetter = $rowLetter;
        $letters = array_values(preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY));
        foreach ($letters as $i => &$letter) {
            $letter = trim($letter);
            $this->phpExcelObject->getActiveSheet()->setCellValue($activeCell, $letter);

            $rowLetterIndex = \PHPExcel_Cell::columnIndexFromString($activeRowLetter);
            $rowLetterIndex += $range;
            $activeRowLetter = \PHPExcel_Cell::stringFromColumnIndex($rowLetterIndex);

            if($breakLineLength && ($i + 1)%$breakLineLength == 0) { //enter to new line
                if($nextCells) {
                    list($activeRowLetter, $lineNumber) = $this->explodeCell(array_shift($nextCells));
                } else {
                    $lineNumber = $lineNumber + 2;
                    $activeRowLetter = $rowLetter;

                }

                if(isset($letters[$i+1]) && trim($letters[$i+1]) == ''){
                    unset($letters[$i+1]);
                }
            }

            $activeCell = $activeRowLetter . $lineNumber;
        }
    }

    /**
     * Letter and number of cell
     * @param $cell
     *
     * @todo use \PHPExcel_Cell::coordinateFromString
     * @see \PHPExcel_Cell::coordinateFromString
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