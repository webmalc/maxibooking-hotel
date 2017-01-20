<?php
/**
 * Created by Zavalyuk Alexandr (Zalex).
 * email: zalex@zalex.com.ua
 * Date: 10/6/16
 * Time: 11:19 AM
 */

namespace MBH\Bundle\OnlineBookingBundle\Lib;


use MBH\Bundle\BaseBundle\Service\Messenger\RecipientInterface;

class OnlineNotifyRecipient implements RecipientInterface
{
    private $email = 'noreply@maxibooking.ru';

    private $name;

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
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommunicationLanguage()
    {
        return $this->language;
    }


}