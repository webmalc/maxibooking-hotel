<?php
/**
 * Created by PhpStorm.
 * Date: 01.06.18
 */

namespace MBH\Bundle\OnlineBundle\Lib;


class SearchForm
{
    /**
     * @var string|null
     */
    private $phoneOrEmail;

    /**
     * @var string|null
     */
    private $numberOrder;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * @var boolean
     */
    private $userNameVisible = false;

    /**
     * @return null|string
     */
    public function getPhoneOrEmail(): ?string
    {
        return $this->phoneOrEmail;
    }

    /**
     * @param null|string $phoneOrEmail
     */
    public function setPhoneOrEmail(?string $phoneOrEmail): void
    {
        $this->phoneOrEmail = $phoneOrEmail;
    }

    /**
     * @return null|string
     */
    public function getNumberOrder(): ?string
    {
        return $this->numberOrder;
    }

    /**
     * @param null|string $numberOrder
     */
    public function setNumberOrder(?string $numberOrder): void
    {
        $this->numberOrder = $numberOrder;
    }

    /**
     * @return null|string
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param null|string $userName
     */
    public function setUserName(?string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return bool
     */
    public function isUserNameVisible(): bool
    {
        return $this->userNameVisible;
    }

    /**
     * @param bool $userNameVisible
     */
    public function setUserNameVisible(bool $userNameVisible): void
    {
        $this->userNameVisible = $userNameVisible;
    }


}