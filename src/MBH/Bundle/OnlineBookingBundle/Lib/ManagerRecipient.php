<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/6/16
 * Time: 11:19 AM
 */

namespace MBH\Bundle\OnlineBookingBundle\Lib;


use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;

class ManagerRecipient implements RecipientInterface
{
    private $email = 'noreply@maxibooking.ru';

    private $name = 'Manager';

    private $language = 'ru';

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getCommunicationLanguage()
    {
        return $this->language;
    }


}