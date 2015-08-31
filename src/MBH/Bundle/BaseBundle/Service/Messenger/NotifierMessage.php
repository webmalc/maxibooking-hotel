<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\HotelBundle\Document\Hotel;

/**
 * NotifierMessage
 */
class NotifierMessage
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $from;

    /**
     * @var RecipientInterface
     */
    private $recipients = [];

    /**
     * @var bool
     */
    private $email = true;

    /**
     * @var string $type info|danger|success|primary
     */
    private $type = 'info';

    /**
     * @var string $category notification|report|error
     */
    private $category = 'notification';

    /**
     * @var string
     */
    private $autohide = false;

    /**
     * @var \DateTime
     */
    private $end = null;

    /**
     * @var string
     */
    private $template = null;

    /**
     * @var Order
     */
    private $order = null;

    /**
     * @var Hotel
     */
    private $hotel = null;

    /**
     * @var string
     */
    private $link = null;

    /**
     * @var string
     */
    private $linkText = null;

    /**
     * @var array
     */
    private $additionalData = [];

    /**
     * @var string
     */
    private $signature = null;

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutohide()
    {
        return $this->autohide;
    }

    /**
     * @param $autohide
     * @return $this
     */
    public function setAutohide($autohide)
    {
        $this->autohide = $autohide;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param $end
     * @return $this
     */
    public function setEnd(\DateTime $end = null)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return RecipientInterface[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param RecipientInterface[] $recipients
     * @return $this
     */
    public function setRecipients(array $recipients = [])
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param RecipientInterface $recipient
     *  [$email, $fullName]
     * @return $this
     */
    public function addRecipient(RecipientInterface $recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param boolean $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return self
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return Hotel
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param Hotel $hotel
     * @return self
     */
    public function setHotel(Hotel $hotel = null)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return self
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return $this->linkText;
    }

    /**
     * @param string $linkText
     * @return self
     */
    public function setLinkText($linkText)
    {
        $this->linkText = $linkText;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @param array $additionalData
     * @return self
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     * @return self
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

}
