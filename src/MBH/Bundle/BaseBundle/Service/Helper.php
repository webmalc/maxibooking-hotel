<?php

namespace MBH\Bundle\BaseBundle\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * Helper service
 */
class Helper
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Component\Translation\IdentityTranslator
     */
    protected $tr;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->tr = $this->container->get('translator');
    }

    /**
     * @param mixed $date
     * @param string $format
     * @param string $timezone
     * @return \DateTime|null
     */
    public static function getDateFromString($date, $format = "d.m.Y", string $timezone = null)
    {
        if (empty($date)) {
            return null;
        }
        if ($date instanceof \DateTime) {
            return $date;
        }

        $timezone = $timezone ?? date_default_timezone_get();

        return \DateTime::createFromFormat($format.' H:i:s', $date.' 00:00:00', new \DateTimeZone($timezone));
    }

    /**
     * @param $collection
     * @param string $method
     * @return array
     */
    public static function toIds($collection, $method = 'getId')
    {
        $result = [];

        foreach ($collection as $object) {
            $result[] = (is_object($object) && method_exists($object, $method)) ? $object->$method() : (string)$object;
        }

        return $result;
    }

    /**
     * @param array $collection
     * @param bool $withMultipleValues
     * @param string $method
     * @return array
     */
    public function sortByValue($collection, $withMultipleValues = false, $method = 'getId')
    {
        $result = [];

        foreach ($collection as $item) {
            if ($withMultipleValues) {
                $result[$item->$method()][] = $item;
            } else {
                $result[$item->$method()] = $item;
            }
        }

        return $result;
    }

    /**
     * @param $collection
     * @param $callback
     * @param bool $withMultipleValues
     * @return array
     */
    public function sortByValueByCallback($collection, $callback, $withMultipleValues = false)
    {
        $result = [];

        foreach ($collection as $item) {
            $key = $callback($item);
            if ($withMultipleValues) {
                $result[$key][] = $item;
            } else {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    /**
     * @param string $text
     * @return string
     */
    public static function translateToLat($text)
    {
        $rus = [
            'А',
            'Б',
            'В',
            'Г',
            'Д',
            'Е',
            'Ё',
            'Ж',
            'З',
            'И',
            'Й',
            'К',
            'Л',
            'М',
            'Н',
            'О',
            'П',
            'Р',
            'С',
            'Т',
            'У',
            'Ф',
            'Х',
            'Ц',
            'Ч',
            'Ш',
            'Щ',
            'Ъ',
            'Ы',
            'Ь',
            'Э',
            'Ю',
            'Я',
            'а',
            'б',
            'в',
            'г',
            'д',
            'е',
            'ё',
            'ж',
            'з',
            'и',
            'й',
            'к',
            'л',
            'м',
            'н',
            'о',
            'п',
            'р',
            'с',
            'т',
            'у',
            'ф',
            'х',
            'ц',
            'ч',
            'ш',
            'щ',
            'ъ',
            'ы',
            'ь',
            'э',
            'ю',
            'я',
            ' ',
            '/',
            '\\',
        ];
        $lat = [
            'A',
            'B',
            'V',
            'G',
            'D',
            'E',
            'E',
            'Gh',
            'Z',
            'I',
            'Y',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'F',
            'H',
            'C',
            'Ch',
            'Sh',
            'Sch',
            'Y',
            'Y',
            'Y',
            'E',
            'Yu',
            'Ya',
            'a',
            'b',
            'v',
            'g',
            'd',
            'e',
            'e',
            'gh',
            'z',
            'i',
            'y',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'f',
            'h',
            'c',
            'ch',
            'sh',
            'sch',
            'y',
            'y',
            'y',
            'e',
            'yu',
            'ya',
            ' ',
            '_',
            '_',
        ];

        return str_replace($rus, $lat, $text);
    }

    /**
     * @param string $interface
     * @return array
     */
    public function getClassesByInterface($interface)
    {
        $result = [];

        foreach (get_declared_classes() as $class) {
            $reflect = new \ReflectionClass($class);
            if ($reflect->implementsInterface($interface)) {
                $result[] = $class;
            }
        }

        return $result;
    }

    /**
     * @param int $length
     * @return string
     *
     */
    public function getRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Returns the amount in words
     * @author runcore
     * @uses morph(...)
     * @param $num
     * @return string
     */
    public function num2str($num)
    {
        $currency = $this->container->get('mbh.currency')->info();
        $nul = 'ноль';
        $translator = $this->container->get('translator');
        $ten = array(
            array(
                '',
                $translator->trans('mbhbasebundle.service.helper.one'),
                $translator->trans('mbhbasebundle.service.helper.dve'),
                $translator->trans('mbhbasebundle.service.helper.tri'),
                $translator->trans('mbhbasebundle.service.helper.chetire'),
                $translator->trans('mbhbasebundle.service.helper.piat'),
                $translator->trans('mbhbasebundle.service.helper.shest'),
                $translator->trans('mbhbasebundle.service.helper.sem'),
                $translator->trans('mbhbasebundle.service.helper.vosem'),
                $translator->trans('mbhbasebundle.service.helper.deviat'),
            ),
            array(
                '',
                $translator->trans('mbhbasebundle.service.helper.odna'),
                $translator->trans('mbhbasebundle.service.helper.dve'),
                $translator->trans('mbhbasebundle.service.helper.tri'),
                $translator->trans('mbhbasebundle.service.helper.chetire'),
                $translator->trans('mbhbasebundle.service.helper.piat'),
                $translator->trans('mbhbasebundle.service.helper.shest'),
                $translator->trans('mbhbasebundle.service.helper.sem'),
                $translator->trans('mbhbasebundle.service.helper.vosem'),
                $translator->trans('mbhbasebundle.service.helper.deviat'),
            ),
        );
        $a20 = array(
            $translator->trans('mbhbasebundle.service.helper.ten'),
            $translator->trans('mbhbasebundle.service.helper.eleven'),
            $translator->trans('mbhbasebundle.service.helper.twelve'),
            $translator->trans('mbhbasebundle.service.helper.fourteen'),
            $translator->trans('mbhbasebundle.service.helper.fifteen'),
            $translator->trans('mbhbasebundle.service.helper.sixteen'),
            $translator->trans('mbhbasebundle.service.helper.seventeen'),
            $translator->trans('mbhbasebundle.service.helper.eighteen'),
            $translator->trans('mbhbasebundle.service.helper.nineteen'),
        );
        $tens = array(
            2 => 'двадцать',
            'тридцать',
            $translator->trans('mbhbasebundle.service.helper.sorok'),
            'пятьдесят',
            'шестьдесят',
            'семьдесят',
            'восемьдесят',
            $translator->trans('mbhbasebundle.service.helper.devyanosto')
        );
        $hundred = array(
            '',
            $translator->trans('mbhbasebundle.service.helper.sto'),
            $translator->trans('mbhbasebundle.service.helper.dvesti'),
            $translator->trans('mbhbasebundle.service.helper.trista'),
            $translator->trans('mbhbasebundle.service.helper.chetyresta'),
            'пятьсот',
            'шестьсот',
            'семьсот',
            'восемьсот',
            'девятьсот'
        );
        $unit = array( // Units
            array($currency['small'], $currency['small'], $currency['small'], 1),
            array($currency['text'], $currency['text'], $currency['text'], 0),
            array('тысяча', 'тысячи', 'тысяч', 1),
            array(
                $translator->trans('mbhbasebundle.service.helper.million'),
                $translator->trans('mbhbasebundle.service.helper.millionа'),
                $translator->trans('mbhbasebundle.service.helper.millionov'), 0),
            array(
                $translator->trans('mbhbasebundle.service.helper.milliard'),
                $translator->trans('mbhbasebundle.service.helper.miliarda'),
                $translator->trans('mbhbasebundle.service.helper.milliardov'), 0),
        );
        //
        list($rub, $kop) = explode('.', sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if (!intval($v)) {
                    continue;
                }
                $uk = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];
                } # 20-99
                else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];
                } # 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) {
                    $out[] = $this->morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        } else {
            $out[] = $nul;
        }
        $out[] = $this->morph(intval($rub), $unit[1][0], $unit[1][1], $unit[1][2]); // rub
        $out[] = $kop . ' ' . $this->morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    /**
     * Склоняем словоформу
     * @ author runcore
     */
    public function morph($n, $f1, $f2, $f5)
    {
        $n = abs(intval($n)) % 100;
        if ($n > 10 && $n < 20) {
            return $f5;
        }
        $n = $n % 10;
        if ($n > 1 && $n < 5) {
            return $f2;
        }
        if ($n == 1) {
            return $f1;
        }

        return $f5;
    }

    public function convertNumberToWords($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );

            return false;
        }

        if ($number < 0) {
            return $negative . $this->convertNumberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convertNumberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convertNumberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public function getMBHBundles()
    {
        $bundles = new \SplObjectStorage();
        $kernelDir = $this->container->get('kernel')->getRootDir();
        $finder = Finder::create()->directories()->name('*')->in($kernelDir.'/../src/MBH/Bundle')->depth(0);
        $kernel = $this->container->get('kernel');
        foreach ($finder as $dir) {
            /** @var \SplFileInfo $dir */
            $dir->isDir() ? $bundles->attach($kernel->getBundle('MBH'.$dir->getBasename())) : null;
        }

        return $bundles;
    }

    /**
     * Get filtered values for the specified filter
     *
     * @param DocumentManager $dm
     * @param  $callback
     * @param bool $isFilterOn
     * @param string $filter
     * @return mixed
     */
    public function getFilteredResult(DocumentManager $dm, $callback, $isFilterOn = true, $filter = 'disableable')
    {
        if ($isFilterOn && !$dm->getFilterCollection()->isEnabled($filter)) {
            $dm->getFilterCollection()->enable($filter);
        }
        $result = $callback();

        if ($isFilterOn && $dm->getFilterCollection()->isEnabled($filter)) {
            $dm->getFilterCollection()->disable($filter);
        }

        return $result;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getDataFromMultipleSelectField($fieldData)
    {
        if (!empty($fieldData) && is_array($fieldData)) {
            foreach ($fieldData as $index => $singleValue) {
                if ($singleValue === '') {
                    unset($fieldData[$index]);
                }
            }

            return $fieldData;
        }

        return [];
    }

    public function getTimeZone(?ClientConfig $clientConfig = null)
    {
        if (is_null($clientConfig)) {
            $clientConfig = $this->container
                ->get('doctrine.odm.mongodb.document_manager')
                ->getRepository('MBHClientBundle:ClientConfig')
                ->fetchConfig();
        }

        if (is_null($clientConfig) || empty($clientConfig->getTimeZone())) {
            return $this->container->getParameter('locale') === 'ru'
                ? 'Europe/Moscow'
                : 'Europe/Paris';
        }

        return $clientConfig->getTimeZone();
    }

    /**
     * @param $string
     * @return int
     */
    public function getFirstNumberFromString(string $string)
    {
        preg_match('/\d+/', $string, $numberMatches);

        return count($numberMatches) > 0 ? $numberMatches[0] : intval($string);
    }
}
