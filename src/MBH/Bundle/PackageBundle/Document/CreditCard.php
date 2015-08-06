<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\BaseBundle\Lib\Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 * @ODM\HasLifecycleCallbacks
 */
class CreditCard implements \JsonSerializable
{
    const CIPHER = MCRYPT_RIJNDAEL_128;
    const MODE = MCRYPT_MODE_CBC;
    const KEY = 'y4rZN5TgyMpfZak4Pyf58zx78iEdujKT';

    /**
     * @var string
     * @ODM\String
     */
    public $type;

    /**
     * @var string
     * @ODM\String
     * @Assert\NotNull(message= "validator.document.card.number")
     * @Assert\Type(type="numeric")
     * @Assert\Length(
     *      min=13,
     *      minMessage= "validator.document.card.number.invalid.min",
     *      max=19,
     *      maxMessage= "validator.document.card.number.invalid.max"
     * )
     */
    public $number;

    /**
     * @var string
     * @ODM\String
     * @Assert\NotNull(message= "validator.document.card.cardholder")
     */
    public $cardholder;

    /**
     * Date format MM/YYYY
     * @var string
     * @ODM\String
     * @Assert\NotNull(message= "validator.document.card.date")
     */
    public $date;

    /**
     * @var string
     * @ODM\String
     * @Assert\Type(type="numeric")
     */
    public $cvc;

    /**
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_DEV_URANDOM);
        $ciphertext = mcrypt_encrypt(self::CIPHER, self::KEY, $plaintext, self::MODE, $iv);
        return base64_encode($iv . $ciphertext);
    }

    /**
     * @param string $ciphertext
     * @return string
     * @throws Exception
     */
    public function decrypt($ciphertext)
    {
        $ciphertext = base64_decode($ciphertext);
        $ivSize = mcrypt_get_iv_size(self::CIPHER, self::MODE);
        if (strlen($ciphertext) < $ivSize) {
            throw new Exception('Missing initialization vector');
        }
        $iv = substr($ciphertext, 0, $ivSize);
        $ciphertext = substr($ciphertext, $ivSize);
        $plaintext = mcrypt_decrypt(self::CIPHER, self::KEY, $ciphertext, self::MODE, $iv);
        return rtrim($plaintext, "\0");
    }

    public function __toString()
    {
        $result = '';

        foreach ($this->toArray() as $prop => $val) {
            $result .= mb_strtoupper($prop) . ': ' . $val . " \n";
        }
        return $result;
    }

    public function toArray()
    {
        return [
            'type' => $this->getType(),
            'number' => $this->getNumber(),
            'date' => $this->getDate(),
            'cardholder' => $this->getCardholder(),
            'cvc' => $this->getCvc(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __call($name, $arguments)
    {
        $prop = mb_strtolower(str_replace('get', '', $name));
        if (!property_exists($this, $prop)) {
            throw new Exception('The property does not exist.');
        }

        return $this->decrypt($this->$prop);
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->encryptAll();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->encryptAll();
    }

    public function encryptAll()
    {
        foreach (get_object_vars($this) as $prop => $val) {
            $this->$prop = $this->encrypt($val);
        }
    }

    public function getYear()
    {
        if (!empty($this->getDate())) {
            $arr = explode('/', $this->getDate());

            if (isset($arr[1])) {
                return $arr[1];
            }
        }

        return null;
    }

    public function getMonth()
    {
        if (!empty($this->getDate())) {
            return explode('/', $this->getDate())[0];

        }

        return null;
    }

    /**
     * @param string $type
     * @return CreditCard
     */
    public function setType($type)
    {
        $this->type = mb_strtolower((string) $type);
        return $this;
    }

    /**
     * @param string $number
     * @return CreditCard
     */
    public function setNumber($number)
    {
        $this->number = preg_replace("/[^0-9]/", "", (string) $number);
        return $this;
    }

    /**
     * @param string $cardholder
     * @return CreditCard
     */
    public function setCardholder($cardholder)
    {
        $this->cardholder = mb_strtoupper((string)$cardholder);
        return $this;
    }

    /**
     * Date format MM/YYYY
     * @param string $date
     * @return CreditCard
     */
    public function setDate($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('m/Y');
        } elseif (preg_match('/^(\d{4}).{1}(\d{2})$/iu', (string) $date, $matches) && !empty($matches[1]) && !empty($matches[2])) {
            $date = $matches[2] . '/' . $matches[1];
        }

        $this->date = (string) $date;
        return $this;
    }

    /**
     * @param int $cvc
     * @return CreditCard
     */
    public function setCvc($cvc)
    {
        $this->cvc = (string) $cvc;
        return $this;
    }
}