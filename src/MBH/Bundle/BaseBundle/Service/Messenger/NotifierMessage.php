<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

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
     * @var array
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
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param array $recipients
     * @return $this
     */
    public function setRecipients(array $recipients = null)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param $recipient
     * @return $this
     */
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
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
     * @return boolean
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

}
