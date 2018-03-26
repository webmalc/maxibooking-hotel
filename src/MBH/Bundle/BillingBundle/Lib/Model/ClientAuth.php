<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


class ClientAuth
{
    private $client;
    private $ip;
    private $auth_date;
    private $user_agent;

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return ClientAuth
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     * @return ClientAuth
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuth_date()
    {
        return $this->auth_date;
    }

    /**
     * @param mixed $auth_date
     * @return ClientAuth
     */
    public function setAuth_date($auth_date)
    {
        $this->auth_date = $auth_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser_agent()
    {
        return $this->user_agent;
    }

    /**
     * @param mixed $user_agent
     * @return ClientAuth
     */
    public function setUser_agent($user_agent)
    {
        $this->user_agent = $user_agent;

        return $this;
    }
}