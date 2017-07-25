<?php


namespace MBH\Bundle\BillingBundle\Lib\Model;


use Symfony\Component\Validator\Constraints as Assert;

class Answer
{
    /**
     * @var bool
     * @Assert\Type(type="boolean")
     * @Assert\NotNull()
     */
    protected $status = false;

    /**
     * @var  string
     * @Assert\Url()
     * @Assert\NotNull(groups={"install"})
     */
    protected $url;

    /**
     * @var string
     * @Assert\Type(type="string")
     * @Assert\NotNull(groups={"install"})
     */
    protected $password;

    /**
     * @var string
     *
     * */
    protected $error;

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError(string $error)
    {
        $this->error = $error;
    }






}