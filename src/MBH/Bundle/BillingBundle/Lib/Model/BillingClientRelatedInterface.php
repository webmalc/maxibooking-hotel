<?php

namespace MBH\Bundle\BillingBundle\Lib\Model;


interface BillingClientRelatedInterface
{
    public function getRequest_client();
    public function setRequest_client(string $login);
}