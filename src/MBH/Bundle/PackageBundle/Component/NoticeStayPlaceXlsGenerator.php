<?php

namespace MBH\Bundle\PackageBundle\Component;


use MBH\Bundle\BaseBundle\Lib\Exception;
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

        $path = realpath(__DIR__.'/../Resources/data/Uvedomlenie_o_pribytii_inostrannogo_grazhdanina_v_mesto_prebyvanija.xls');
        $this->phpExcelObject = $this->phpExcel->createPHPExcelObject($path);
    }

    public function generateResponse()
    {
        /*$this->phpExcelObject->setActiveSheetIndex()
            ->setCellValue('W13', 'А')
            ->setCellValue('AA13', 'Р')
            ->setCellValue('AE13', 'О')
            ->setCellValue('AI13', 'Ф')
            ->setCellValue('AM13', 'И');

        $this->phpExcelObject->setActiveSheetIndex(1)
            ->setCellValue('AE14', 'М')
            ->setCellValue('AI14', 'О')
            ->setCellValue('AM14', 'С')
            ->setCellValue('AQ14', 'К')
            ->setCellValue('AU14', 'О');*/


        $this->write('Иванов Иван Анатольевич', 'W13');
        //$this->write('', 'W15');

        $this->write('Российская федерация', 'AA16');
        $this->write('03', 'AE21');
        $this->write('12', 'AU21');
        $this->write('1988', 'BG21');
        $this->phpExcelObject->getActiveSheet()->setCellValue('CY21', 'X');

        $this->write('Россия г.Москва ул.Дмитровская д.23 кв.2', 'AE24');
        $this->write('Паспорт', 'BC30');
        $this->write('3587', 'DC37');
        $this->write('78574324', 'DW37');


        $this->write('20', 'AA40');
        $this->write('05', 'AQ40');
        $this->write('2011', 'BC40');

        $this->write('20', 'CM37');
        $this->write('05', 'DC37');
        $this->write('2021', 'DO37');

        $this->phpExcelObject->getActiveSheet()->setCellValue('BO43', 'X');

        $this->write('Инженер', 'W45');

        $this->write('20', 'AI47');
        $this->write('07', 'AY47');
        $this->write('2015', 'BK47');

        $this->write('01', 'DO47');
        $this->write('08', 'EE47');
        $this->write('2015', 'EQ47');

        $this->write('5653', 'AQ49');
        $this->write('2698432', 'BK49');

        $this->write('Сведения о законных', 'AA51');

        $this->write('Россия г.Москва', 'AA57');

        $this->write('Иванов Иван Анатольевич', 'W69');
        $this->write('Российская федерация', 'AA74');

        $this->write('03', 'AE77');
        $this->write('12', 'AU77');
        $this->write('1988', 'BG77');

        $this->phpExcelObject->getActiveSheet()->setCellValue('DC77', 'X');

        $this->write('Паспорт', 'BC80');
        $this->write('3587', 'DC80');
        $this->write('78574324', 'DW80');

        $this->write('Краснодарский край', 'AE83');
        $this->write('Новая улица', 'W91');

        $this->write('32', 'S93');
        $this->write('4', 'AQ93');
        $this->write('', 'BS93');
        $this->write('37', 'CU93');

        $this->write('01', 'AQ95');
        $this->write('08', 'BG95');
        $this->write('2015', 'BS95');


        // two sheet
        $this->phpExcelObject->setActiveSheetIndex(1);

        $this->write('Краснодарский край', 'AE14');
        $this->write('Новая улица', 'W22');
        $this->write('3', 'S24');
        $this->write('12', 'AQ24');
        $this->write('1', 'BS24');
        $this->write('10', 'CU24');
        $this->write('94957548864', 'DS24');
        $this->phpExcelObject->getActiveSheet()->setCellValue('EE26', 'X');

        $this->write('Киселев Владимир', 'W28');
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

        $this->write('Гостинечный комплекс', 'AA51');
        $this->write('9584765377', 'S60');

        $this->write('01', 'DO69');
        $this->write('08', 'EE69');
        $this->write('2015', 'EQ69');

        $this->write('Киселев Владимир', 'W71');
        $this->write('Петрович', 'W73');

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