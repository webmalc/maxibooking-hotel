<?php

namespace MBH\Bundle\UserBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument()
 * Class AuthorizationToken
 * @package MBH\Bundle\UserBundle\Document
 */
class AuthorizationToken
{
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $token;

    /**
     * @var \DateTime
     * @ODM\Field(type="date")
     */
    private $expiredAt;

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return AuthorizationToken
     */
    public function setToken(string $token): AuthorizationToken
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt(): ?\DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     * @return AuthorizationToken
     */
    public function setExpiredAt(\DateTime $expiredAt): AuthorizationToken
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}