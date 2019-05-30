<?php


namespace MBH\Bundle\UserBundle\Document;


use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gesdinet\JWTRefreshTokenBundle\Document\AbstractRefreshToken;

/**
 * Class JwtRefreshToken
 * @package MBH\Bundle\UserBundle\Document
 * @MongoDB\Document(collection="UserRefreshToken")
 */
class JwtRefreshToken extends AbstractRefreshToken
{

    /** @var string
     * @MongoDB\Id(strategy="auto")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

}