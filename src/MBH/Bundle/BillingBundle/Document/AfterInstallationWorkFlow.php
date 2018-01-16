<?php


namespace MBH\Bundle\BillingBundle\Document;

use MBH\Bundle\BillingBundle\Lib\InstallWorkflowInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;


/**
 * Class InstallationWorkflow
 * @package MBH\Bundle\BillingBundle\Document
 * @ODM\Document(collection="AfterInstallationWorkflows")
 */
class AfterInstallationWorkFlow extends InstallationWorkflow implements InstallWorkflowInterface
{

}