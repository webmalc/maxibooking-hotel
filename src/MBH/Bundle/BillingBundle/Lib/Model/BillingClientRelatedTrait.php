<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;

trait BillingClientRelatedTrait
{
    private $request_client;

    /**
     * @return string
     */
    public function getRequest_client()
    {
        return $this->request_client;
    }

    /**
     * @param string $client
     * @return static
     */
    public function setRequest_client(?string $client)
    {
        $this->request_client = $client;

        return $this;
    }
}